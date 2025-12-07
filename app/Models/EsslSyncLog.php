<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EsslSyncLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_serial',
        'device_name',
        'source_ip',
        'total_events',
        'stored_records',
        'skipped_manual',
        'missing_employees',
        'invalid_events',
        'error_events',
        'status',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];
}
