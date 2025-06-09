<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoftwareAssignment extends Model
{    
    use HasFactory;

    protected $fillable = [
        'reference_no',
        'user_id',
        'admin_id',
        'software_id',
        'purpose',
        'remarks',
        'date_assigned',
    ];

    protected $casts = [
        'date_assigned' => 'datetime',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function software()
    {
        return $this->belongsTo(Software::class);
    }
}
