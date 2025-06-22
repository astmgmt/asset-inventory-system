<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetQrcodeLogAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_qrcode_log_id',
        'asset_id',
    ];

    public function assetQrcodeLog()
    {
        return $this->belongsTo(AssetQrcodeLog::class);
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
