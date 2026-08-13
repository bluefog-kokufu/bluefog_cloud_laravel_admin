<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'published_at',
        'title',
        'content',
        'is_active',
    ];

    protected $casts = [
        'published_at' => 'date',
        'is_active' => 'boolean',
    ];
}
