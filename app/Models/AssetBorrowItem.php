<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetBorrowItem extends Model
{

    protected $fillable = [
        'borrow_transaction_id',
        'asset_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function transaction()
    {
        return $this->belongsTo(AssetBorrowTransaction::class, 'borrow_transaction_id');
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function returnItems()
    {
        return $this->hasMany(AssetReturnItem::class, 'borrow_item_id');
    }

    public function penalties()
    {
        return $this->hasMany(Penalty::class, 'borrow_item_id');
    }

}
