<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserHistory extends Model
{
    protected $fillable = [
        'user_id',
        'borrow_code',
        'return_code',
        'status',
        'borrow_data',
        'return_data',
        'action_date'
    ];

    protected $casts = [
        'borrow_data' => 'array',
        'return_data' => 'array',
        'action_date' => 'datetime',
        'status' => 'string',
    ];
}
