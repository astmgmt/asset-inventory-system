<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class Software extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'software_code',
        'software_name',
        'description',
        'quantity',
        'reserved_quantity',
        'license_key',
        'expiry_date',
        'added_by',
        'expiry_flag',
        'expiry_status',
        'last_notified_at',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'expiry_flag' => 'boolean',
        'last_notified_at' => 'datetime',
    ];
    
    public function updateExpiryStatus()
    {
        $now = now();
        $expiryDate = $this->expiry_date;

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

    public function printLogs()
    {
        return $this->belongsToMany(SoftwarePrintLog::class, 'software_print_log_software', 'software_id', 'software_print_log_id')->withTimestamps();
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function assignmentItems()
    {
        return $this->hasMany(SoftwareAssignmentItem::class, 'software_id');
    }

}
