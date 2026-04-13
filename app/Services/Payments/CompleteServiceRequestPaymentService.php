<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Notifications\NewServiceRequestNotification;
use Illuminate\Support\Facades\DB;

/**
 * Marks a payment completed, sets submitted_at on first completion, and notifies office staff once.
 */
class CompleteServiceRequestPaymentService
{
    public function complete(Payment $payment, array $attributes = []): void
    {
        if ($payment->status === 'completed') {
            return;
        }

        DB::transaction(function () use ($payment, $attributes) {
            $payment->refresh();

            if ($payment->status === 'completed') {
                return;
            }

            $request = $payment->serviceRequest()
                ->with(['service', 'governmentOffice', 'citizen'])
                ->firstOrFail();

            $base = [
                'status' => 'completed',
                'paid_at' => now(),
            ];
            if (isset($attributes['gateway_response']) && is_array($attributes['gateway_response'])) {
                $attributes['gateway_response'] = array_merge(
                    $payment->gateway_response ?? [],
                    $attributes['gateway_response']
                );
            }

            $payment->update(array_merge($base, $attributes));

            $needsSubmit = $request->submitted_at === null;
            if ($needsSubmit) {
                $request->update(['submitted_at' => now()]);
            }

            if ($needsSubmit && $request->citizen && $request->governmentOffice && $request->service) {
                $request->governmentOffice->staff()->each(function ($staff) use ($request) {
                    $staff->notify(new NewServiceRequestNotification(
                        $request->citizen->name,
                        $request->service->name,
                        $request->id,
                        $request->government_office_id,
                    ));
                });
            }
        });
    }
}
