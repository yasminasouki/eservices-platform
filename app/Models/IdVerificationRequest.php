<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdVerificationRequest extends Model
{
    protected $fillable = [
        'user_id',
        'id_document_path',
        'id_document_back_path',
        'api_provider',
        'api_response',
        'api_response_back',
        'extracted_name',
        'extracted_father_name',
        'extracted_mother_name',
        'extracted_place_of_birth',
        'extracted_gender',
        'extracted_dob',
        'extracted_id_number',
        'extracted_registry_number',
        'extracted_issue_date',
        'extracted_expiry_date',
        'extracted_blood_type',
        'extracted_marital_status',
        'extracted_locality',
        'extracted_governorate',
        'extracted_district',
        'status',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'api_response'         => 'array',
            'api_response_back'    => 'array',
            'extracted_dob'        => 'date',
            'extracted_issue_date' => 'date',
            'extracted_expiry_date'=> 'date',
            'verified_at'          => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
