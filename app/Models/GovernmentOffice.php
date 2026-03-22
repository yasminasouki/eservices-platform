<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class GovernmentOffice extends Model
{
    protected $fillable = [
        'name',
        'address',
        'email',
        'phone',
        'website',
        'google_maps_url',
        'latitude',
        'longitude',
        'working_hours',
        'contact_info',
        'is_active',
        'municipality_id',
    ];

    protected function casts(): array
    {
        return [
            'working_hours' => 'array',
            'contact_info'  => 'array',
            'latitude'      => 'float',
            'longitude'     => 'float',
            'is_active'     => 'boolean',
        ];
    }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    /** Municipality this office belongs to */
    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }

    /** Staff members assigned to this office (via pivot) */
    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'office_user_assignments'
        )->withPivot('role_in_office')->withTimestamps();
    }

    /** Assignment records for this office */
    public function officeUserAssignments(): HasMany
    {
        return $this->hasMany(OfficeUserAssignment::class);
    }

    /** Service categories offered by this office */
    public function serviceCategories(): HasMany
    {
        return $this->hasMany(ServiceCategory::class);
    }

    /** Services offered by this office */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    /** All service requests directed to this office */
    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }

    /** Officer time slots defined for this office */
    public function timeSlots(): HasMany
    {
        return $this->hasMany(OfficerTimeSlot::class);
    }

    /** Appointments booked at this office */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /** Feedback submitted about this office */
    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    /**
     * All payments received by this office (through service requests).
     * Used for: revenue reports per office (Admin requirement).
     */
    public function payments(): HasManyThrough
    {
        return $this->hasManyThrough(Payment::class, ServiceRequest::class);
    }

    /**
     * All documents attached to this office's requests (through service requests).
     * Used for: office document management view.
     */
    public function documents(): HasManyThrough
    {
        return $this->hasManyThrough(Document::class, ServiceRequest::class);
    }

    /**
     * All chat messages in the context of this office's requests (through service requests).
     * Used for: Chat & Support — office message inbox view.
     */
    public function messages(): HasManyThrough
    {
        return $this->hasManyThrough(Message::class, ServiceRequest::class);
    }
}
