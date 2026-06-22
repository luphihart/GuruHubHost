<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUuids;

    protected $fillable = [
        'email',
        'password_hash',
        'role',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected function casts(): array
    {
        return [
            'password_hash' => 'hashed',
        ];
    }

    /**
     * Override standard password field for authentication
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
