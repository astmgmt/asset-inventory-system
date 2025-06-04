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

    /**
     * Custom profile photo update method
     */
    public function updateProfilePhoto(UploadedFile $photo)
    {
        // Generate custom filename: originalname_userid.extension
        $originalName = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $photo->extension();
        $filename = $this->sanitizeFilename($originalName) . '_' . $this->id . '.' . $extension;
        
        // Store with custom name
        $path = $photo->storeAs(
            'profile-photos',
            $filename,
            ['disk' => $this->profilePhotoDisk()]
        );

        $this->forceFill([
            'profile_photo_path' => $path,
        ])->save();
    }

    /**
     * Sanitize filename for safe storage
     */
    protected function sanitizeFilename(string $filename): string
    {
        // Remove special characters except dashes and underscores
        $safe = preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);
        
        // Replace multiple underscores with single
        $safe = preg_replace('/_{2,}/', '_', $safe);
        
        // Trim to 100 characters max
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
}