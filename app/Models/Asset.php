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
        'is_disposed'
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
        return $this->belongsTo(AssetCondition::class);
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
}
