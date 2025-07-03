<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoftwarePrintLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'print_code',
        'date_from',
        'date_to',
        'user_id',
        'software_snapshot_data',
    ];

    protected $casts = [
        'software_snapshot_data' => 'array', 
        'date_from' => 'date',
        'date_to' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function software()
    {
        return $this->belongsToMany(Software::class, 'software_print_log_software', 'software_print_log_id', 'software_id');
    }
}