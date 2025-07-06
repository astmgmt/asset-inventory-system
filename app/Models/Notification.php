<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'type_id',
        'message',
        'voice_alert',
        'email_alert',
        'sms_alert',
    ];

    public function type()
    {
        return $this->belongsTo(NotificationType::class, 'type_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_notifications')
            ->withPivot(['is_read', 'notified_at'])
            ->withTimestamps();
    }
}
