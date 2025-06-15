<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Software extends Model
{
    use HasFactory;

    protected $fillable = [
        'software_code',
        'software_name',
        'description',
        'license_key',
        'installation_date',
        'expiry_date',
        'responsible_user_id',
        'expiry_flag',       // Add this
        'expiry_status',     // Add this
        'last_notified_at',  // Add this
    ];

    protected $casts = [
        'installation_date' => 'date',
        'expiry_date' => 'date'
    ];
    
    public function responsibleUser()
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }
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
}
