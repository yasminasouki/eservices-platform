<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'name',
        'service_category_id',
        'price',
        'duration',
        'duration_unit',
        'required_documents',
        'description',
        'is_active',
        'government_office_id',
    ];

    protected function casts(): array
    {
        return [
            'required_documents' => 'array',
            'price'              => 'decimal:2',
            'is_active'          => 'boolean',
        ];
    }

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function governmentOffice()
    {
        return $this->belongsTo(GovernmentOffice::class);
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }
}
