<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetReturnItem extends Model
{

    protected $fillable = [
        'borrow_item_id',
        'returned_by_user_id',
        'returned_by_department_id',
        'return_date',
        'notes',
    ];

    protected $casts = [
        'return_date' => 'datetime',
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

}
