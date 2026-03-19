<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GovernmentOffice extends Model
{
    protected $fillable = [
        'name',
        'address',
        'email',
        'website',
        'maps_location',
        'latitude',
        'longitude',
        'working_hours',
        'contact_info',
        'is_active',
        'municipality_id',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'working_hours' => 'array',
            'contact_info'  => 'array',
            'latitude'      => 'decimal:7',
            'longitude'     => 'decimal:7',
            'is_active'     => 'boolean',
        ];
    }

    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'office_user_assignments')
                    ->withPivot('role_in_office')
                    ->withTimestamps();
    }

    public function serviceCategories()
    {
        return $this->hasMany(ServiceCategory::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function officerTimeSlots()
    {
        return $this->hasMany(OfficerTimeSlot::class);
    }

    public function feedback()
    {
        return $this->hasMany(Feedback::class);
    }
}
