<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    protected $fillable = [
        'name',
        'description',
        'government_office_id',
    ];

    public function governmentOffice()
    {
        return $this->belongsTo(GovernmentOffice::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }
}
