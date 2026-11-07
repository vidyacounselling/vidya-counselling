<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'bio', 'credentials'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, \Illuminate\Database\Eloquent\SoftDeletes;

    public function isAdministrator(): bool { return $this->role === 'administrator' && $this->active && ! $this->trashed(); }
    public function isStaff(): bool { return $this->active && ! $this->trashed() && in_array($this->role, ['administrator','editor'], true); }
    public function posts() { return $this->hasMany(Post::class, 'author_id'); }
    public function canManageInquiries(): bool { return $this->isStaff() && ($this->isAdministrator() || (bool)$this->can_manage_inquiries); }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'active' => 'boolean',
            'can_manage_inquiries' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }
}
