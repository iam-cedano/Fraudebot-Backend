<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'actor_id',
        'action',
        'method',
        'path',
        'query_hash',
        'ip_hash',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'status' => 'integer',
    ];
}
