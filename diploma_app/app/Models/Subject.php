<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $code_name
 */
class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
    ];
}
