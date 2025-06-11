<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenaltyPayment extends Model
{

    protected $fillable = [
        'penalty_id',
        'paid_by_user_id',
        'paid_by_department_id',
        'payment_date',
        'amount_paid',
        'payment_method',
        'remarks',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'amount_paid' => 'decimal:2',
    ];

    public function penalty()
    {
        return $this->belongsTo(Penalty::class);
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by_user_id');
    }

    public function paidByDepartment()
    {
        return $this->belongsTo(Department::class, 'paid_by_department_id');
    }
}
