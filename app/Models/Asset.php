<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    /** @use HasFactory<\Database\Factories\AssetFactory> */
    use HasFactory;
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
        'is_disposed',
        'expiry_flag',       
        'expiry_status',     
        'last_notified_at', 
    ];

    protected $casts = [
        'warranty_expiration' => 'date',
    ];

    // Add this accessor to format the warranty date
    protected function formattedWarranty(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->warranty_expiration->format('M d, Y'),
        );
    }

    // Add this method to check if warranty is expiring
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
        return $this->belongsTo(AssetLocation::class);
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
}
