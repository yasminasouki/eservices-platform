<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'phone',
        'profile_photo',
        'id_document',
        'id_document_status',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'social_provider',
        'social_provider_id',
        'last_login_at',
        'must_change_password',
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
            'two_factor_confirmed_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeCitizens($query)
    {
        return $query->where('role', 'citizen');
    }

    public function scopeOfficeUsers($query)
    {
        return $query->where('role', 'office_user');
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    // ──────────────────────────────────────────────
    // Account status helpers
    // ──────────────────────────────────────────────

    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }

    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    // ──────────────────────────────────────────────
    // Role helpers
    // ──────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isOfficeUser(): bool
    {
        return $this->role === 'office_user';
    }

    public function isCitizen(): bool
    {
        return $this->role === 'citizen';
    }

    /**
     * Government office IDs this staff member may act for (municipality portal).
     *
     * @return list<int>
     */
    public function assignedGovernmentOfficeIds(): array
    {
        if (! $this->isOfficeUser()) {
            return [];
        }

        return $this->governmentOffices()->pluck('government_offices.id')->all();
    }

    /** First assigned office; used when the UI assumes a single office per user. */
    public function primaryGovernmentOffice(): ?GovernmentOffice
    {
        if (! $this->isOfficeUser()) {
            return null;
        }

        return $this->governmentOffices()->orderBy('government_offices.name')->first();
    }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    /** Municipalities this admin manages (admin has full system control over all) */
    public function administeredMunicipalities(): HasMany
    {
        return $this->hasMany(Municipality::class, 'admin_user_id');
    }

    /** Government offices under this admin's municipalities */
    public function administeredOffices(): HasManyThrough
    {
        return $this->hasManyThrough(
            GovernmentOffice::class,
            Municipality::class,
            'admin_user_id',   // FK on municipalities → users
            'municipality_id', // FK on government_offices → municipalities
        );
    }

    /** Office-user assignments (which offices this user is assigned to) */
    public function officeAssignments(): HasMany
    {
        return $this->hasMany(OfficeUserAssignment::class);
    }

    /** Government offices this user is assigned to (via pivot) */
    public function governmentOffices(): BelongsToMany
    {
        return $this->belongsToMany(
            GovernmentOffice::class,
            'office_user_assignments'
        )->withPivot('role_in_office')->withTimestamps();
    }

    /** Service requests submitted by this citizen */
    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }

    /** Service requests where this user is the assigned officer */
    public function assignedRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class, 'assigned_officer_id');
    }

    /** Time slots owned by this officer */
    public function timeSlots(): HasMany
    {
        return $this->hasMany(OfficerTimeSlot::class);
    }

    /** Appointments assigned to this officer (through their time slots) */
    public function officerAppointments(): HasManyThrough
    {
        return $this->hasManyThrough(Appointment::class, OfficerTimeSlot::class);
    }

    /** Appointments booked by this citizen */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /** Payments made by this user */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /** Documents uploaded by this user (their own uploads only) */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * All documents on this citizen's service requests —
     * includes both citizen-uploaded docs AND office-generated
     * certificates, receipts, and approvals.
     * Used for: "Download certificates, receipts, and completed request documents"
     */
    public function requestDocuments(): HasManyThrough
    {
        return $this->hasManyThrough(Document::class, ServiceRequest::class);
    }

    /** Feedback submitted by this citizen */
    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    /** Messages sent by this user */
    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /** Messages received by this user */
    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    /** Push notification device tokens */
    public function deviceTokens(): HasMany
    {
        return $this->hasMany(DeviceToken::class);
    }

    /** ID verification requests submitted by this user */
    public function idVerificationRequests(): HasMany
    {
        return $this->hasMany(IdVerificationRequest::class);
    }

    /** Most recent ID verification request (used to check signup verification status) */
    public function latestIdVerification(): HasOne
    {
        return $this->hasOne(IdVerificationRequest::class)->latestOfMany();
    }

    /** Status changes authored by this user */
    public function statusLogChanges(): HasMany
    {
        return $this->hasMany(ServiceRequestStatusLog::class, 'changed_by');
    }
}
