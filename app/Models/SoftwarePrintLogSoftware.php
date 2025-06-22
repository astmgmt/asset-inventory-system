<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoftwarePrintLogSoftware extends Model
{    
    use HasFactory;

    protected $fillable = [
        'software_print_log_id',
        'software_id',
    ];

    public function softwarePrintLog()
    {
        return $this->belongsTo(SoftwarePrintLog::class);
    }

    public function software()
    {
        return $this->belongsTo(Software::class);
    }

}
