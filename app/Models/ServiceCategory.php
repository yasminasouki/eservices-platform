<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceCategory extends Model
{
    protected $fillable = [
        'name',
        'description',
        'government_office_id',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function governmentOffice(): BelongsTo
    {
        return $this->belongsTo(GovernmentOffice::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }
}
