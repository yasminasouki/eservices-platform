<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'duration',
        'duration_unit',
        'required_documents',
        'is_active',
        'service_category_id',
        'government_office_id',
    ];

    protected function casts(): array
    {
        return [
            'price'              => 'decimal:2',
            'required_documents' => 'array',
            'is_active'          => 'boolean',
        ];
    }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function governmentOffice(): BelongsTo
    {
        return $this->belongsTo(GovernmentOffice::class);
    }

    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }
}
