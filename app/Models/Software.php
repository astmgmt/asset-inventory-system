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
    ];

    protected $casts = [
        'installation_date' => 'date',
        'expiry_date' => 'date'
    ];
    
    public function responsibleUser()
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }
}
