<?php

declare(strict_types=1);

namespace App\IATI\Models\ApiLog;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ApiLog.
 */
class ApiLog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'method',
        'url',
        'request',
        'response',
        'ip_address',
        'user_agent',
        'content_type',
    ];

    /**
     * The attributes that should be cast to certain data types.
     *
     * @var array
     */
    protected $casts = [
        'request'  => 'encrypted:array',
        'response' => 'encrypted:array',
    ];
    /**
     * @var string
     */
    protected $table = 'ryd_api_logs';
}
