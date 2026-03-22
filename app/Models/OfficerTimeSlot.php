<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OfficerTimeSlot extends Model
{
    protected $fillable = [
        'government_office_id',
        'user_id',
        'date',
        'start_time',
        'end_time',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'date'         => 'date',
            'is_available' => 'boolean',
        ];
    }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function governmentOffice(): BelongsTo
    {
        return $this->belongsTo(GovernmentOffice::class);
    }

    /** The officer assigned to this slot */
    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Appointment that occupies this slot (one-to-one) */
    public function appointment(): HasOne
    {
        return $this->hasOne(Appointment::class);
    }
}
