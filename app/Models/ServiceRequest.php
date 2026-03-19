<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    protected $fillable = [
        'status',
        'qr_code',
        'notes',
        'rejection_reason',
        'missing_docs_note',
        'submitted_at',
        'reviewed_at',
        'completed_at',
        'user_id',
        'service_id',
        'government_office_id',
        'assigned_officer_id',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'reviewed_at'  => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function governmentOffice()
    {
        return $this->belongsTo(GovernmentOffice::class);
    }

    public function assignedOfficer()
    {
        return $this->belongsTo(User::class, 'assigned_officer_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function appointment()
    {
        return $this->hasOne(Appointment::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function feedback()
    {
        return $this->hasOne(Feedback::class);
    }

    public function userNotifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function statusLogs()
    {
        return $this->hasMany(ServiceRequestStatusLog::class);
    }
}
