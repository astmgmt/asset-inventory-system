<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetReturnItem extends Model
{

    protected $fillable = [
        'return_code',
        'borrow_item_id',
        'returned_by_user_id',
        'returned_by_department_id',
        'returned_at',
        'remarks',
        'approval_status',
        'approved_at',
        'approved_by_user_id',
    ];

    protected $casts = [
        'returned_at' => 'datetime',
        'updated_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function borrowItem()
    {
        return $this->belongsTo(AssetBorrowItem::class);
    }

    public function returnedBy()
    {
        return $this->belongsTo(User::class, 'returned_by_user_id');
    }

    public function returnedByDepartment()
    {
        return $this->belongsTo(Department::class, 'returned_by_department_id');
    }
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

}
