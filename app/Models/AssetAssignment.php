<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_no',
        'user_id',
        'admin_id',
        'asset_id',
        'purpose',
        'remarks',
        'date_assigned',
    ];

    protected $casts = [
        'date_assigned' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
