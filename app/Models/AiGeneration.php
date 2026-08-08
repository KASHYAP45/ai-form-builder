<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiGeneration extends Model
{
    protected $fillable = [
        'prompt',
        'status',
        'model',
        'input_tokens',
        'output_tokens',
        'total_tokens',
        'latency_ms',
        'result',
        'error',
    ];

    protected $casts = [
        'result' => 'array',
    ];
}