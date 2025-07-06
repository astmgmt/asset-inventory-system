<?php

namespace App\Models;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Role;
use App\Models\Software;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    protected $fillable = [
        'role_id',
        'name',
        'username',
        'email',
        'contact_number',
        'address',
        'password',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $appends = [
        'profile_photo_url',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
   
    public function updateProfilePhoto(UploadedFile $photo)
    {
        $originalName = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $photo->extension();
        $filename = $this->sanitizeFilename($originalName) . '_' . $this->id . '.' . $extension;
        
        $path = $photo->storeAs(
            'profile-photos',
            $filename,
            ['disk' => $this->profilePhotoDisk()]
        );

        $this->forceFill([
            'profile_photo_path' => $path,
        ])->save();
    }

    protected function sanitizeFilename(string $filename): string
    {
        $safe = preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);        
        $safe = preg_replace('/_{2,}/', '_', $safe);        
        return substr($safe, 0, 100);
    }

    public function role() {
        return $this->belongsTo(Role::class);
    }

    public function isSuperAdmin() {
        return $this->role->name === 'Super Admin';
    }

    public function isAdmin() {
        return $this->role->name === 'Admin';
    }
    
    public function isUser(): bool
    {
        return $this->role && $this->role->name === 'User';
    }

    public function managedSoftware()
    {
        return $this->hasMany(Software::class, 'responsible_user_id');
    }
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function borrowTransactions()
    {
        return $this->hasMany(AssetBorrowTransaction::class);
    }

    public function borrowRequests()
    {
        return $this->hasMany(AssetBorrowTransaction::class, 'requested_by_user_id');
    }

    public function approvedBorrows()
    {
        return $this->hasMany(AssetBorrowTransaction::class, 'approved_by_user_id');
    }

    public function histories()
    {
        return $this->hasMany(UserHistory::class);
    }

    public function notifications()
    {
        return $this->belongsToMany(\App\Models\Notification::class, 'user_notifications')
            ->withPivot(['is_read', 'notified_at'])
            ->withTimestamps();
    }

    public function unreadEmailNotifications()
    {
        return $this->notifications()
            ->whereHas('type', function($q) {
                $q->where('type_name', 'email_notification');
            })
            ->wherePivot('is_read', false);
    }
    public function unreadSuperAdminNotifications()
    {
        return $this->notifications()
            ->whereHas('type', function($q) {
                $q->where('type_name', 'super_admin_email_notification');
            })
            ->wherePivot('is_read', false);
    }
}