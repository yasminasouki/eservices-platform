<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'description',
        'type',
        'uploaded_by',
        'service_request_id',
        'user_id',
    ];

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
