<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BorrowAssetQuantity extends Model
{

    protected $fillable = [
        'asset_id',
        'available_quantity',
    ];

    protected $casts = [
        'available_quantity' => 'integer',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

}
