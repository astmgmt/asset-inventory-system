<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetPrintLog extends Model
{
    
    use HasFactory;

    protected $fillable = [
        'print_code',
        'date_from',
        'date_to',
        'user_id',
        'asset_snapshot_data',
    ];

    protected $casts = [
        'asset_snapshot_data' => 'array',
        'date_from' => 'date',
        'date_to' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assets()
    {
        return $this->belongsToMany(Asset::class, 'asset_print_log_assets')
                    ->withTimestamps();
    }
}
