<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationType extends Model
{
    use HasFactory;
    
    protected $fillable = ['type_name'];

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'type_id');
    }
}
