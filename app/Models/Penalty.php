<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penalty extends Model
{

    protected $fillable = [
        'borrow_item_id',
        'user_id',
        'user_department_id',
        'days_late',
        'amount',
        'is_paid',
    ];

    protected $casts = [
        'days_late' => 'integer',
        'amount' => 'decimal:2',
        'is_paid' => 'boolean',
    ];

    public function borrowItem()
    {
        return $this->belongsTo(AssetBorrowItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userDepartment()
    {
        return $this->belongsTo(Department::class, 'user_department_id');
    }

    public function payments()
    {
        return $this->hasMany(PenaltyPayment::class, 'penalty_id');
    }

}
