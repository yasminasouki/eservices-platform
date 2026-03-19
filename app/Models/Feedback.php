<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $fillable = [
        'rating',
        'comment',
        'office_reply',
        'reply_is_public',
        'replied_at',
        'user_id',
        'government_office_id',
        'service_request_id',
    ];

    protected function casts(): array
    {
        return [
            'reply_is_public' => 'boolean',
            'replied_at'      => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function governmentOffice()
    {
        return $this->belongsTo(GovernmentOffice::class);
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }
}
