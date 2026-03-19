<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'amount',
        'method',
        'status',
        'currency',
        'exchange_rate',
        'transaction_id',
        'crypto_wallet_address',
        'gateway_response',
        'paid_at',
        'service_request_id',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'amount'           => 'decimal:2',
            'exchange_rate'    => 'decimal:8',
            'paid_at'          => 'datetime',
            'gateway_response' => 'array',
        ];
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
