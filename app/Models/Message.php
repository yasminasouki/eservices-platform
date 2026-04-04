<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'service_request_id',
        'government_office_id',
        'body',
        'is_read',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function governmentOffice(): BelongsTo
    {
        return $this->belongsTo(GovernmentOffice::class);
    }

    /**
     * Live chat between one citizen and an office (not tied to a service request).
     *
     * @param  Builder<Message>  $query
     */
    public function scopeForOfficeCitizenThread(Builder $query, int $governmentOfficeId, int $citizenUserId): Builder
    {
        return $query
            ->where('government_office_id', $governmentOfficeId)
            ->whereNull('service_request_id')
            ->where(function (Builder $q) use ($citizenUserId) {
                $q->where('sender_id', $citizenUserId)
                    ->orWhere('receiver_id', $citizenUserId);
            });
    }
}
