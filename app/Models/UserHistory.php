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
        'action_date',
        'read_at',
    ];

    protected $casts = [
        'borrow_data' => 'array',
        'return_data' => 'array',
        'action_date' => 'datetime',
        'read_at' => 'datetime',
        'status' => 'string',
    ];

    public function borrowTransaction()
    {
        return $this->belongsTo(AssetBorrowTransaction::class, 'borrow_code', 'borrow_code');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
