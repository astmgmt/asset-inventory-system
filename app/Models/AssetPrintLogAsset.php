<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetPrintLogAsset extends Model
{
   
    use HasFactory;

    protected $fillable = [
        'asset_print_log_id',
        'asset_id',
    ];

    public function printLog()
    {
        return $this->belongsTo(AssetPrintLog::class, 'asset_print_log_id');
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

}
