<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    protected $fillable = [
        'user_id',
        'government_office_id',
        'officer_time_slot_id',
        'service_request_id',
        'status',
        'notes',
        'cancellation_reason',
        'reminder_sent_at',
        'confirmed_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'reminder_sent_at' => 'datetime',
            'confirmed_at'     => 'datetime',
            'cancelled_at'     => 'datetime',
        ];
    }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    /** Citizen who booked this appointment */
    public function citizen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function governmentOffice(): BelongsTo
    {
        return $this->belongsTo(GovernmentOffice::class);
    }

    /** The specific time slot reserved */
    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(OfficerTimeSlot::class, 'officer_time_slot_id');
    }

    /** Linked service request (optional) */
    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }
}
