<?php

namespace App;

use App;
use Illuminate\Database\Eloquent\Model;

class Drawing extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'result' => 'array'
    ];
}
