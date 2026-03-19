<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficerTimeSlot extends Model
{
    protected $fillable = [
        'date',
        'start_time',
        'end_time',
        'is_available',
        'government_office_id',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'date'         => 'date',
            'is_available' => 'boolean',
        ];
    }

    public function governmentOffice()
    {
        return $this->belongsTo(GovernmentOffice::class);
    }

    public function officer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function appointment()
    {
        return $this->hasOne(Appointment::class);
    }
}
