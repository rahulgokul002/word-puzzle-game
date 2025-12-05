<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    protected $fillable = [
        'word',
        'puzzle_id',
        'user_id',
        'score',
        'remaining_letters',
    ];
}
