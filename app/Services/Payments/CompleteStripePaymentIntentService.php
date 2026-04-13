<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Stripe\PaymentIntent;

/**
 * Verifies a succeeded Stripe PaymentIntent and completes the local payment record (idempotent).
 */
class CompleteStripePaymentIntentService
{
    public function __construct(
        private readonly CompleteServiceRequestPaymentService $completePayment,
    ) {}

    /**
     * @param  int|null  $authUserId  If set, metadata user_id must match (browser flows). Null for webhooks.
     */
    public function tryComplete(Payment $payment, PaymentIntent $pi, ?int $authUserId): bool
    {
        if ($payment->status === 'completed') {
            return true;
        }

        $expectedCents = (int) round((float) $payment->amount * 100);
        if ((int) $pi->amount !== $expectedCents) {
            Log::warning('Stripe PaymentIntent amount mismatch.', [
                'payment_id' => $payment->id,
                'expected' => $expectedCents,
                'actual' => $pi->amount,
            ]);

            return false;
        }

        if ((int) ($pi->metadata->payment_id ?? 0) !== (int) $payment->id) {
            return false;
        }

        if ($authUserId !== null && (int) ($pi->metadata->user_id ?? 0) !== $authUserId) {
            return false;
        }

        if ($pi->status !== 'succeeded') {
            return false;
        }

        $this->completePayment->complete($payment, [
            'transaction_id' => $pi->id,
            'gateway_response' => array_merge($payment->gateway_response ?? [], [
                'stripe_payment_intent_id' => $pi->id,
                'stripe_payment_status' => $pi->status,
            ]),
        ]);

        return true;
    }
}
