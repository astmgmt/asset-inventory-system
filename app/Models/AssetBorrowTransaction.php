<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetBorrowTransaction extends Model
{    
    use HasFactory;
    protected $fillable = [
        'borrow_code',
        'user_id',
        'user_department_id',
        'requested_by_user_id',
        'requested_by_department_id',
        'approved_by_user_id',
        'approved_by_department_id',
        'borrowed_at',
        'approved_at',        
        'status',
        'remarks',
        'return_requested_at',
        'return_remarks',
    ];

    protected $casts = [
        'borrowed_at' => 'datetime',
        'approved_at' => 'datetime',
        'return_requested_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userDepartment()
    {
        return $this->belongsTo(Department::class, 'user_department_id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function requestedByDepartment()
    {
        return $this->belongsTo(Department::class, 'requested_by_department_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function approvedByDepartment()
    {
        return $this->belongsTo(Department::class, 'approved_by_department_id');
    }

    public function borrowItems()
    {
        return $this->hasMany(AssetBorrowItem::class, 'borrow_transaction_id');
    }

}
