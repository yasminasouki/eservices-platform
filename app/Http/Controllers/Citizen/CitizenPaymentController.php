<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\ServiceRequest;
use App\Services\CurrencyExchangeService;
use App\Services\Payments\CompleteServiceRequestPaymentService;
use App\Services\Payments\CompleteStripePaymentIntentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\StripeClient;

class CitizenPaymentController extends Controller
{
    public function __construct(
        private readonly CurrencyExchangeService $exchange,
        private readonly CompleteServiceRequestPaymentService $completePayment,
        private readonly CompleteStripePaymentIntentService $stripeIntentCompletion,
    ) {}

    public function pay(ServiceRequest $serviceRequest): View|RedirectResponse
    {
        $this->authorizeCitizen($serviceRequest);

        if (! $serviceRequest->requiresCitizenPayment()) {
            return redirect()->route('citizen.requests.show', $serviceRequest);
        }

        if (! $serviceRequest->awaitingCitizenPayment()) {
            return redirect()->route('citizen.requests.show', $serviceRequest);
        }

        $serviceRequest->load(['service', 'payment', 'governmentOffice']);

        $payment = $serviceRequest->payment;
        if (! $payment) {
            abort(404, 'Payment record missing for this request.');
        }

        $eurRate = null;
        $eurAmount = null;
        try {
            $eurRate = $this->exchange->usdToEurRate();
            $eurAmount = round((float) $payment->amount * $eurRate, 2);
        } catch (RuntimeException $e) {
            Log::warning('EUR reference rate unavailable.', ['error' => $e->getMessage()]);
        }

        $cryptoSessionKey = 'crypto_quote_'.$serviceRequest->id;
        $cryptoQuote = session($cryptoSessionKey);
        if (! is_array($cryptoQuote) && $payment->method === 'cryptocurrency') {
            $cryptoQuote = $this->hydrateCryptoSessionFromPayment($payment, $cryptoSessionKey);
        }

        if (is_array($cryptoQuote)) {
            $quotedAt = $this->parseCryptoQuotedAt($payment->gateway_response ?? []);
            $ttlMinutes = (int) config('payments.crypto.quote_valid_for_minutes', 45);
            if ($quotedAt === null || $quotedAt->copy()->addMinutes($ttlMinutes)->isPast()) {
                session()->forget($cryptoSessionKey);
                $cryptoQuote = null;
            }
        }

        $stripeSecretOk = $this->stripeSecret() !== '';
        $stripePublishableOk = $this->stripePublishableKey() !== '';
        $unitCents = (int) round((float) $payment->amount * 100);
        $stripeAmountOk = $unitCents >= 50;

        $stripePaymentIntentClientSecret = null;
        $stripeElementsError = null;

        if ($stripeSecretOk && $stripePublishableOk && $stripeAmountOk) {
            try {
                $stripePaymentIntentClientSecret = $this->ensureStripePaymentIntent($payment, $serviceRequest);
            } catch (ApiErrorException $e) {
                Log::error('Stripe PaymentIntent failed.', ['error' => $e->getMessage()]);
                $stripeElementsError = 'Could not start card payment. Try again or use cryptocurrency.';
            }
        } elseif ($stripeSecretOk && $stripePublishableOk && ! $stripeAmountOk) {
            $stripeElementsError = 'Amount is too small for card payment (Stripe minimum is US$0.50).';
        }

        $stripeElementsReady = $stripeSecretOk && $stripePublishableOk && $stripeAmountOk
            && $stripePaymentIntentClientSecret !== null;

        return view('citizen.payments.checkout', [
            'request' => $serviceRequest,
            'payment' => $payment,
            'eurRate' => $eurRate,
            'eurAmount' => $eurAmount,
            'cryptoQuote' => is_array($cryptoQuote) ? $cryptoQuote : null,
            'stripePublishableKey' => $stripePublishableOk ? $this->stripePublishableKey() : '',
            'stripePaymentIntentClientSecret' => $stripePaymentIntentClientSecret,
            'stripeElementsReady' => $stripeElementsReady,
            'stripeSecretConfigured' => $stripeSecretOk,
            'stripePublishableConfigured' => $stripePublishableOk,
            'cryptoAddresses' => $this->cryptoAddresses(),
            'showDemoCryptoNotice' => (bool) config('payments.crypto.show_demo_crypto_notice'),
            'stripeElementsError' => $stripeElementsError,
            'cryptoQuoteExpiresAt' => is_array($cryptoQuote)
                ? $this->cryptoQuoteExpiresAtIso($payment)
                : null,
        ]);
    }

    /**
     * JSON: after Stripe.js confirms the PaymentIntent on the client, verify and complete server-side.
     */
    public function confirmStripeElements(Request $request, ServiceRequest $serviceRequest): JsonResponse
    {
        $this->authorizeCitizen($serviceRequest);

        if (! $serviceRequest->awaitingCitizenPayment()) {
            return response()->json([
                'ok' => false,
                'message' => 'This request is not awaiting payment.',
            ], 422);
        }

        $validated = $request->validate([
            'payment_intent_id' => ['required', 'string', 'max:255'],
        ]);

        $payment = $serviceRequest->payment;
        if (! $payment) {
            return response()->json(['ok' => false, 'message' => 'Payment not found.'], 404);
        }

        $stripe = new StripeClient($this->stripeSecret());

        try {
            /** @var PaymentIntent $pi */
            $pi = $stripe->paymentIntents->retrieve($validated['payment_intent_id']);
        } catch (ApiErrorException $e) {
            return response()->json(['ok' => false, 'message' => 'Could not verify payment.'], 422);
        }

        if ($this->stripeIntentCompletion->tryComplete($payment, $pi, (int) auth()->id())) {
            session()->forget('crypto_quote_'.$serviceRequest->id);

            return response()->json([
                'ok' => true,
                'redirect' => route('citizen.requests.show', $serviceRequest),
            ]);
        }

        return response()->json([
            'ok' => false,
            'message' => 'Payment is not completed yet. If you were verifying a bank card, finish any prompts from your bank and try again.',
        ], 422);
    }

    /**
     * Return URL after 3-D Secure or similar redirects from Stripe.
     */
    public function stripeElementsReturn(Request $request): RedirectResponse
    {
        $piId = (string) $request->query('payment_intent', '');
        if ($piId === '') {
            return redirect()->route('citizen.dashboard')->withErrors(['pay' => 'Missing payment confirmation.']);
        }

        if ($this->stripeSecret() === '') {
            return redirect()->route('citizen.dashboard')->withErrors(['pay' => 'Stripe is not configured.']);
        }

        $stripe = new StripeClient($this->stripeSecret());

        try {
            /** @var PaymentIntent $pi */
            $pi = $stripe->paymentIntents->retrieve($piId);
        } catch (ApiErrorException $e) {
            return redirect()->route('citizen.dashboard')->withErrors(['pay' => 'Could not verify payment.']);
        }

        $paymentId = (int) ($pi->metadata->payment_id ?? 0);
        $payment = Payment::query()->find($paymentId);

        if (! $payment || (int) $payment->user_id !== (int) auth()->id()) {
            abort(403);
        }

        $serviceRequest = $payment->serviceRequest;
        if (! $serviceRequest) {
            abort(404);
        }

        if ($this->stripeIntentCompletion->tryComplete($payment, $pi, (int) auth()->id())) {
            session()->forget('crypto_quote_'.$serviceRequest->id);

            return redirect()
                ->route('citizen.requests.show', $serviceRequest)
                ->with('success', 'Payment received. Your request is now with the office.');
        }

        return redirect()
            ->route('citizen.requests.pay', $serviceRequest)
            ->withErrors(['stripe' => 'Payment was not completed. You can try again on the payment page.']);
    }

    public function stripeReturn(Request $request): RedirectResponse
    {
        $sessionId = (string) $request->query('session_id', '');
        if ($sessionId === '') {
            return redirect()->route('citizen.dashboard')->withErrors(['pay' => 'Missing payment session.']);
        }

        $secret = $this->stripeSecret();
        if ($secret === '') {
            return redirect()->route('citizen.dashboard')->withErrors(['pay' => 'Stripe is not configured.']);
        }

        $stripe = new StripeClient($secret);

        try {
            $session = $stripe->checkout->sessions->retrieve($sessionId);
        } catch (ApiErrorException $e) {
            return redirect()->route('citizen.dashboard')->withErrors(['pay' => 'Could not verify payment.']);
        }

        $paymentId = (int) ($session->metadata->payment_id ?? 0);
        $payment = Payment::query()->find($paymentId);

        if (! $payment || (int) $payment->user_id !== (int) auth()->id()) {
            abort(403);
        }

        $expectedCents = (int) round((float) $payment->amount * 100);
        if ((int) $session->amount_total !== $expectedCents) {
            abort(400, 'Paid amount does not match invoice.');
        }

        if ($session->payment_status !== 'paid') {
            return redirect()->route('citizen.requests.pay', $payment->serviceRequest)
                ->withErrors(['stripe' => 'Payment was not completed.']);
        }

        $pi = $session->payment_intent ?? null;
        $txId = is_string($pi) ? $pi : (is_object($pi) ? $pi->id : $session->id);

        $this->completePayment->complete($payment, [
            'transaction_id' => $txId,
            'gateway_response' => array_merge($payment->gateway_response ?? [], [
                'stripe_checkout_session_id' => $session->id,
                'stripe_payment_status' => $session->payment_status,
            ]),
        ]);

        return redirect()
            ->route('citizen.requests.show', $payment->serviceRequest)
            ->with('success', 'Payment received. Your request is now with the office.');
    }

    public function cryptoQuote(Request $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        $this->authorizeCitizen($serviceRequest);

        if (! $serviceRequest->awaitingCitizenPayment()) {
            return redirect()->route('citizen.requests.show', $serviceRequest);
        }

        $validated = $request->validate([
            'asset' => ['required', 'string', 'in:btc,eth,usdt'],
        ]);

        $payment = $serviceRequest->payment;
        if (! $payment) {
            abort(404);
        }

        $addresses = $this->cryptoAddresses();
        $asset = $validated['asset'];
        $walletKey = match ($asset) {
            'btc' => 'btc',
            'eth' => 'eth',
            'usdt' => 'usdt',
            default => null,
        };
        if ($walletKey === null || ($addresses[$walletKey] ?? '') === '') {
            return back()->withErrors(['crypto' => 'This cryptocurrency is not configured for receiving payments yet.']);
        }

        try {
            $quote = $this->exchange->quoteCryptoFromUsd($asset, (float) $payment->amount);
        } catch (RuntimeException $e) {
            return back()->withErrors(['crypto' => 'Could not load an exchange rate. Try again shortly.']);
        }

        $gateway = array_merge($payment->gateway_response ?? [], [
            'crypto_quote' => $quote,
            'crypto_wallet' => $addresses[$walletKey],
            'crypto_quoted_at' => now()->toIso8601String(),
        ]);
        foreach (['crypto_tx_reference', 'crypto_confirmed_by_citizen_at', 'crypto_citizen_submitted_at'] as $k) {
            unset($gateway[$k]);
        }

        $payment->update([
            'method' => 'cryptocurrency',
            'exchange_rate' => $quote['usd_per_unit'],
            'crypto_wallet_address' => $addresses[$walletKey],
            'gateway_response' => $gateway,
        ]);

        session([
            'crypto_quote_'.$serviceRequest->id => array_merge($quote, [
                'wallet' => $addresses[$walletKey],
            ]),
        ]);

        return redirect()
            ->to(route('citizen.requests.pay', $serviceRequest) . '?tab=crypto')
            ->with('success', 'Crypto amount updated below. Send exactly that amount to the address shown.');
    }

    public function cryptoConfirm(Request $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        $this->authorizeCitizen($serviceRequest);

        if (! $serviceRequest->awaitingCitizenPayment()) {
            return redirect()->route('citizen.requests.show', $serviceRequest);
        }

        $payment = $serviceRequest->payment;
        $hasCryptoQuote = $payment
            && isset($payment->gateway_response['crypto_quote'])
            && isset($payment->gateway_response['crypto_wallet']);
        if (! $hasCryptoQuote) {
            return back()->withErrors(['crypto' => 'Generate a cryptocurrency quote first.']);
        }

        if ($payment->status === 'completed') {
            return redirect()
                ->route('citizen.requests.show', $serviceRequest)
                ->with('info', 'This payment is already completed.');
        }

        $quotedAt = $this->parseCryptoQuotedAt($payment->gateway_response ?? []);
        if ($quotedAt === null) {
            return back()->withErrors(['crypto' => 'Generate a fresh cryptocurrency quote before confirming.']);
        }

        $ttlMinutes = (int) config('payments.crypto.quote_valid_for_minutes', 45);
        if ($quotedAt->copy()->addMinutes($ttlMinutes)->isPast()) {
            session()->forget('crypto_quote_'.$serviceRequest->id);

            return back()->withErrors([
                'crypto' => 'This quote has expired. Click "Get quote" again for a current amount and rate.',
            ]);
        }

        $validated = $request->validate([
            'tx_reference' => ['nullable', 'string', 'max:500'],
        ]);

        $ref = $this->normalizeCryptoTxReference($validated['tx_reference'] ?? null);
        $autoComplete = (bool) config('payments.crypto.auto_complete_after_citizen_submit', false);
        $requireRef = ! $autoComplete
            && (bool) config('payments.crypto.require_tx_reference_when_manual_verify', true);
        $minLen = (int) config('payments.crypto.tx_reference_min_length', 10);

        if ($requireRef && (strlen((string) $ref) < $minLen)) {
            return back()->withErrors([
                'crypto' => 'Please paste your transaction hash or explorer link (at least '.$minLen.' characters) so the office can verify your transfer.',
            ]);
        }

        $existingSubmit = $payment->gateway_response['crypto_citizen_submitted_at'] ?? null;
        if (! $autoComplete && $existingSubmit && $ref === null) {
            return redirect()
                ->to(route('citizen.requests.pay', $serviceRequest) . '?tab=crypto')
                ->with('info', 'We already recorded your payment notice. If you need to add a transaction reference, paste it below and submit again.');
        }

        $gateway = array_merge($payment->gateway_response ?? [], [
            'crypto_tx_reference' => $ref,
            'crypto_confirmed_by_citizen_at' => now()->toIso8601String(),
            'crypto_citizen_submitted_at' => $payment->gateway_response['crypto_citizen_submitted_at'] ?? now()->toIso8601String(),
        ]);

        $transactionId = $this->uniqueCryptoTransactionId($payment, $ref);

        if ($autoComplete) {
            $this->completePayment->complete($payment, [
                'transaction_id' => $transactionId,
                'gateway_response' => $gateway,
            ]);

            session()->forget('crypto_quote_'.$serviceRequest->id);

            return redirect()
                ->route('citizen.requests.show', $serviceRequest)
                ->with('success', 'Payment recorded. Your request is now with the office.');
        }

        $payment->update(['gateway_response' => $gateway]);

        return redirect()
            ->to(route('citizen.requests.pay', $serviceRequest) . '?tab=crypto')
            ->with('info', 'Reference saved. The office will verify the transfer on-chain; you will be notified when the request is activated.');
    }

    private function authorizeCitizen(ServiceRequest $serviceRequest): void
    {
        abort_unless((int) $serviceRequest->user_id === (int) auth()->id(), 403);
    }

    private function stripeSecret(): string
    {
        return trim((string) config('payments.stripe.secret'));
    }

    private function stripePublishableKey(): string
    {
        return trim((string) config('payments.stripe.publishable_key'));
    }

    /**
     * @return array{btc: string, eth: string, usdt: string}
     */
    private function cryptoAddresses(): array
    {
        return [
            'btc' => trim((string) config('payments.crypto.btc_address')),
            'eth' => trim((string) config('payments.crypto.eth_address')),
            'usdt' => trim((string) config('payments.crypto.usdt_erc20_address')),
        ];
    }

    /**
     * Restore session quote payload from the payment row (e.g. new session / expired session cookie).
     *
     * @return array<string, mixed>|null
     */
    private function hydrateCryptoSessionFromPayment(Payment $payment, string $sessionKey): ?array
    {
        $gw = $payment->gateway_response ?? [];
        $quote = $gw['crypto_quote'] ?? null;
        if (! is_array($quote)) {
            return null;
        }

        $wallet = $gw['crypto_wallet'] ?? $payment->crypto_wallet_address;
        if (! is_string($wallet) || $wallet === '') {
            return null;
        }

        $payload = array_merge($quote, ['wallet' => $wallet]);
        session([$sessionKey => $payload]);

        return $payload;
    }

    private function cryptoQuoteExpiresAtIso(Payment $payment): ?string
    {
        $parsed = $this->parseCryptoQuotedAt($payment->gateway_response ?? []);
        if ($parsed === null) {
            return null;
        }
        $ttl = (int) config('payments.crypto.quote_valid_for_minutes', 45);

        return $parsed->copy()->addMinutes($ttl)->timezone(config('app.timezone'))->toIso8601String();
    }

    /**
     * @param  array<string, mixed>  $gatewayResponse
     */
    private function parseCryptoQuotedAt(array $gatewayResponse): ?Carbon
    {
        $raw = $gatewayResponse['crypto_quoted_at'] ?? null;
        if (! is_string($raw) || $raw === '') {
            return null;
        }

        try {
            return Carbon::parse($raw);
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizeCryptoTxReference(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }

        $s = trim($raw);
        if ($s === '') {
            return null;
        }

        if (strlen($s) > 500) {
            $s = substr($s, 0, 500);
        }

        if (preg_match('/0x[a-fA-F0-9]{64}/', $s, $m)) {
            return strtolower($m[0]);
        }

        if (preg_match('/\bbc1[a-z0-9]{20,120}\b/i', $s, $m)) {
            return strtolower($m[0]);
        }

        if (preg_match('/\b(tb1|bc1)[a-z0-9]{20,120}\b/i', $s, $m)) {
            return strtolower($m[0]);
        }

        if (preg_match('/(?<![0-9a-fA-F])([a-fA-F0-9]{64})(?![0-9a-fA-F])/', $s, $m)) {
            return strtolower($m[1]);
        }

        return $s;
    }

    private function uniqueCryptoTransactionId(Payment $payment, ?string $normalizedRef): string
    {
        if (is_string($normalizedRef) && $normalizedRef !== '') {
            $base = 'crypto:'.$payment->id.':'.$normalizedRef;

            return strlen($base) <= 255 ? $base : substr($base, 0, 255);
        }

        return 'crypto:'.$payment->id.':'.Str::lower(Str::random(26));
    }

    /**
     * Create or reuse a PaymentIntent for Stripe Elements (card number / expiry / CVC).
     */
    private function ensureStripePaymentIntent(Payment $payment, ServiceRequest $serviceRequest): string
    {
        $stripe = new StripeClient($this->stripeSecret());
        $unitCents = (int) round((float) $payment->amount * 100);

        $existingId = $payment->gateway_response['stripe_elements_intent_id'] ?? null;
        if (is_string($existingId) && $existingId !== '') {
            try {
                /** @var PaymentIntent $existing */
                $existing = $stripe->paymentIntents->retrieve($existingId);
                if (
                    $existing->status === 'requires_payment_method'
                    || $existing->status === 'requires_confirmation'
                ) {
                    if ((int) $existing->amount === $unitCents) {
                        $metaPid = (int) ($existing->metadata->payment_id ?? 0);
                        if ($metaPid === (int) $payment->id) {
                            $updates = ['gateway_response' => array_merge($payment->gateway_response ?? [], [
                                'stripe_elements_intent_id' => $existing->id,
                            ])];
                            if ($payment->method !== 'cryptocurrency') {
                                $updates['method'] = 'card';
                            }
                            $payment->update($updates);

                            return (string) $existing->client_secret;
                        }
                    }
                }
            } catch (ApiErrorException) {
                // Create a fresh intent below
            }
        }

        $intent = $stripe->paymentIntents->create([
            'amount' => $unitCents,
            'currency' => 'usd',
            'payment_method_types' => ['card'],
            'metadata' => [
                'payment_id' => (string) $payment->id,
                'service_request_id' => (string) $serviceRequest->id,
                'user_id' => (string) auth()->id(),
            ],
        ]);

        $updates = ['gateway_response' => array_merge($payment->gateway_response ?? [], [
            'stripe_elements_intent_id' => $intent->id,
            'stripe_elements_created_at' => now()->toIso8601String(),
        ])];
        if ($payment->method !== 'cryptocurrency') {
            $updates['method'] = 'card';
        }
        $payment->update($updates);

        return (string) $intent->client_secret;
    }
}
