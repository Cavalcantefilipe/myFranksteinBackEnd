<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Battle extends Model
{
    protected $fillable = [
        'blue_team',
        'red_team',
        'winner',
        'turns',
        'log',
    ];

    protected $casts = [
        'blue_team' => 'array',
        'red_team' => 'array',
        'log' => 'array',
        'turns' => 'integer',
    ];
}
