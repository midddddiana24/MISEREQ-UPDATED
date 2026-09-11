<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClarificationAttachment extends Model
{
    protected $fillable = [
        'service_request_clarification_id', 'uploaded_by', 'path', 'original_name', 'mime_type', 'size',
    ];

    public function clarification(): BelongsTo
    {
        return $this->belongsTo(ServiceRequestClarification::class, 'service_request_clarification_id');
    }
}
