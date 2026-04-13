<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'service_request_id',
        'user_id',
        'amount',
        'currency',
        'exchange_rate',
        'method',
        'status',
        'transaction_id',
        'stripe_checkout_session_id',
        'crypto_wallet_address',
        'gateway_response',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'exchange_rate' => 'decimal:6',
            'gateway_response' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    /** Citizen who made the payment */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
