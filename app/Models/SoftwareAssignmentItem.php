<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SoftwareAssignmentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_batch_id',
        'software_id',
        'quantity',
        'status',
        'remarks',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function batch()
    {
        return $this->belongsTo(SoftwareAssignmentBatch::class, 'assignment_batch_id');
    }

    public function software()
    {
        return $this->belongsTo(Software::class, 'software_id');
    }
}
