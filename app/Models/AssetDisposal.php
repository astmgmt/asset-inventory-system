<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetDisposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'disposed_by',
        'disposal_date',
        'method',
        'vendor_id',
        'document_path',
        'status',
        'approved_by',
        'approved_at',
        'reason',
        'notes',
    ];

    protected $casts = [
        'disposal_date' => 'date',
        'approved_at' => 'datetime',
        'method' => 'string',
        'status' => 'string',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function disposer()
    {
        return $this->belongsTo(User::class, 'disposed_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
