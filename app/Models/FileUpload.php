<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileUpload extends Model
{
    protected $fillable = [
        'file_name',
        'original_name',
        'status',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];
}
