<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'status',
        'notes',
        'cancellation_reason',
        'reminder_sent_at',
        'confirmed_at',
        'cancelled_at',
        'user_id',
        'government_office_id',
        'officer_time_slot_id',
        'service_request_id',
    ];

    protected function casts(): array
    {
        return [
            'reminder_sent_at' => 'datetime',
            'confirmed_at'     => 'datetime',
            'cancelled_at'     => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function governmentOffice()
    {
        return $this->belongsTo(GovernmentOffice::class);
    }

    public function timeSlot()
    {
        return $this->belongsTo(OfficerTimeSlot::class, 'officer_time_slot_id');
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }
}
