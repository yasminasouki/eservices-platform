<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ServiceRequest extends Model
{
    /** Values must match `service_requests.status` enum in migrations. */
    public const STATUSES = [
        'pending',
        'in_review',
        'missing_documents',
        'approved',
        'rejected',
        'completed',
    ];

    protected $fillable = [
        'user_id',
        'service_id',
        'government_office_id',
        'assigned_officer_id',
        'status',
        'qr_code',
        'notes',
        'rejection_reason',
        'missing_docs_note',
        'submitted_at',
        'reviewed_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at'  => 'datetime',
            'reviewed_at'   => 'datetime',
            'completed_at'  => 'datetime',
        ];
    }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    /** Citizen who submitted this request */
    public function citizen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Service being requested */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /** Office handling this request */
    public function governmentOffice(): BelongsTo
    {
        return $this->belongsTo(GovernmentOffice::class);
    }

    /** Officer assigned to process this request */
    public function assignedOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_officer_id');
    }

    /** Payment for this request */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    /** Appointment linked to this request */
    public function appointment(): HasOne
    {
        return $this->hasOne(Appointment::class);
    }

    /** Documents attached to this request */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /** Feedback submitted for this request */
    public function feedback(): HasOne
    {
        return $this->hasOne(Feedback::class);
    }

    /** Notifications triggered by this request */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /** Chat messages in the context of this request */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /** Audit trail of status changes */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(ServiceRequestStatusLog::class);
    }
}
