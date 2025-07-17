<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'asset_code',
        'name',
        'description',
        'quantity',
        'serial_number',
        'model_number',
        'category_id',
        'condition_id',
        'location_id',
        'vendor_id',
        'warranty_expiration',
        'date_acquired',
        'is_disposed',
        'expiry_flag',       
        'expiry_status',     
        'last_notified_at', 
        'show_status',
    ];

    protected $casts = [
        'warranty_expiration' => 'date',
        'date_acquired' => 'date',
    ];

    protected function formattedWarranty(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->warranty_expiration->format('M d, Y'),
        );
    }

    public function isWarrantyExpiring()
    {
        return $this->warranty_expiration < now()->addDays(30);
    }

    public function category()
    {
        return $this->belongsTo(AssetCategory::class);
    }

    public function condition()
    {
        return $this->belongsTo(AssetCondition::class, 'condition_id');
    }

    public function location()
    {
        return $this->belongsTo(AssetLocation::class, 'location_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
    public function assignments()
    {
        return $this->hasMany(AssetAssignment::class);
    }

    public function borrowAssetQuantity()
    {
        return $this->hasOne(BorrowAssetQuantity::class);
    }
    public function updateExpiryStatus()
    {
        $now = now();
        $expiryDate = $this->warranty_expiration;

        if ($expiryDate <= $now) {
            $this->expiry_status = 'expired';
            $this->expiry_flag = true;
        } elseif ($expiryDate <= $now->copy()->addMonth(1)) {
            $this->expiry_status = 'warning_1m';
            $this->expiry_flag = true;
        } elseif ($expiryDate <= $now->copy()->addMonths(2)) {
            $this->expiry_status = 'warning_2m';
            $this->expiry_flag = true;
        } elseif ($expiryDate <= $now->copy()->addMonths(3)) {
            $this->expiry_status = 'warning_3m';
            $this->expiry_flag = true;
        } else {
            $this->expiry_status = 'active';
            $this->expiry_flag = false;
        }

        $this->save();
    }
    public function getAvailableQuantityAttribute()
    {
        return $this->quantity - $this->reserved_quantity;
    }
    public function qrcodeLogs()
    {
        return $this->belongsToMany(AssetQrcodeLog::class, 'asset_qrcode_log_assets', 'asset_id', 'asset_qrcode_log_id')->withTimestamps();
    }
}
