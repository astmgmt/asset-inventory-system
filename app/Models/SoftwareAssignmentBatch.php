<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SoftwareAssignmentBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_no',
        'user_id',
        'assigned_by',
        'approved_by',
        'purpose',
        'remarks',
        'status',
        'date_assigned',
        'approved_at',
    ];

    protected $casts = [
        'date_assigned' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedByUser()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function assignmentItems()
    {
        return $this->hasMany(SoftwareAssignmentItem::class, 'assignment_batch_id');
    }
}
