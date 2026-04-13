<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Payments\CompleteServiceRequestPaymentService;
use App\Services\Payments\CompleteStripePaymentIntentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function handle(
        Request $request,
        CompleteServiceRequestPaymentService $completePayment,
        CompleteStripePaymentIntentService $completeIntent,
    ): Response {
        $secret = trim((string) config('payments.stripe.webhook_secret'));
        if ($secret === '') {
            Log::warning('Stripe webhook received but STRIPE_WEBHOOK_SECRET is empty.');

            return response('Webhook not configured', 503);
        }

        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature', '');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (UnexpectedValueException|SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature invalid.', ['error' => $e->getMessage()]);

            return response('Invalid signature', 400);
        }

        if ($event->type === 'checkout.session.completed') {
            /** @var Session $session */
            $session = $event->data->object;
            $paymentId = (int) ($session->metadata->payment_id ?? 0);
            $payment = Payment::query()->find($paymentId);

            if ($payment && $session->payment_status === 'paid') {
                $expectedCents = (int) round((float) $payment->amount * 100);
                if ((int) $session->amount_total === $expectedCents) {
                    $pi = $session->payment_intent ?? null;
                    $txId = is_string($pi) ? $pi : (is_object($pi) ? $pi->id : $session->id);
                    $completePayment->complete($payment, [
                        'transaction_id' => $txId,
                        'stripe_checkout_session_id' => $session->id,
                        'gateway_response' => array_merge($payment->gateway_response ?? [], [
                            'stripe_webhook_event_id' => $event->id,
                            'stripe_checkout_session_id' => $session->id,
                        ]),
                    ]);
                }
            }
        }

        if ($event->type === 'payment_intent.succeeded') {
            /** @var PaymentIntent $pi */
            $pi = $event->data->object;
            $paymentId = (int) ($pi->metadata->payment_id ?? 0);
            $payment = Payment::query()->find($paymentId);

            if ($payment) {
                $gatewayExtra = [
                    'stripe_webhook_event_id' => $event->id,
                ];
                if ($completeIntent->tryComplete($payment, $pi, null)) {
                    $payment->refresh();
                    $payment->update([
                        'gateway_response' => array_merge($payment->gateway_response ?? [], $gatewayExtra),
                    ]);
                }
            }
        }

        return response('OK', 200);
    }
}
