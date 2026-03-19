<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Municipality extends Model
{
    protected $fillable = [
        'name',
        'region',
        'admin_user_id',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    public function governmentOffices()
    {
        return $this->hasMany(GovernmentOffice::class);
    }
}
