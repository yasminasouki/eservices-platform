<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdVerificationRequest extends Model
{
    protected $fillable = [
        'user_id',
        'id_document_path',
        'api_provider',
        'api_response',
        'extracted_name',
        'extracted_dob',
        'extracted_id_number',
        'status',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'api_response'  => 'array',
            'extracted_dob' => 'date',
            'verified_at'   => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
