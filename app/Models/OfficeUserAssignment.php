<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficeUserAssignment extends Model
{
    protected $fillable = [
        'government_office_id',
        'user_id',
        'role_in_office',
    ];

    public function governmentOffice()
    {
        return $this->belongsTo(GovernmentOffice::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
