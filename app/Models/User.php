<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'phone',
        'id_document',
        'id_document_status',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'social_provider',
        'social_provider_id',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
            'two_factor_enabled' => 'boolean',
            'two_factor_recovery_codes' => 'array',
        ];
    }

    // Relationships
    public function administeredMunicipality()
    {
        return $this->hasOne(Municipality::class, 'admin_user_id');
    }

    public function governmentOffice()
    {
        return $this->hasOne(GovernmentOffice::class);
    }

    public function officeAssignments()
    {
        return $this->hasMany(OfficeUserAssignment::class);
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function assignedRequests()
    {
        return $this->hasMany(ServiceRequest::class, 'assigned_officer_id');
    }

    public function timeSlots()
    {
        return $this->hasMany(OfficerTimeSlot::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function feedback()
    {
        return $this->hasMany(Feedback::class);
    }

    public function userNotifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function deviceTokens()
    {
        return $this->hasMany(DeviceToken::class);
    }

    public function idVerificationRequests()
    {
        return $this->hasMany(IdVerificationRequest::class);
    }

    public function statusLogChanges()
    {
        return $this->hasMany(ServiceRequestStatusLog::class, 'changed_by');
    }
}
