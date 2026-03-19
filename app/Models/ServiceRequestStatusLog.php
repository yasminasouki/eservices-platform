<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRequestStatusLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'service_request_id',
        'changed_by',
        'from_status',
        'to_status',
        'notes',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
