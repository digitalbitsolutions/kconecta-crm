<?php

namespace App\Models;

use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, MustVerifyEmailTrait, Notifiable;

    public const LEVEL_ADMIN = 1;
    public const LEVEL_FREE = 2;
    public const LEVEL_PREMIUM = 3;
    public const LEVEL_SERVICE_PROVIDER = 4;
    public const LEVEL_AGENT = 5;

    protected $table = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'user_name',
        'email',
        'phone',
        'landline_phone',
        'document_type',
        'document_number',
        'address',
        'password',
        'photo',
        'user_level_id',
        'is_active',
        'email_verified_at',
        'remember_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'is_active' => 'boolean',
        'email_verified_at' => 'datetime',
    ];

    public function isAdmin(): bool
    {
        return (int) $this->user_level_id === self::LEVEL_ADMIN;
    }

    public function isServiceProvider(): bool
    {
        return (int) $this->user_level_id === self::LEVEL_SERVICE_PROVIDER;
    }

    public function isPropertyUser(): bool
    {
        return in_array((int) $this->user_level_id, [
            self::LEVEL_FREE,
            self::LEVEL_PREMIUM,
            self::LEVEL_AGENT,
        ], true);
    }

    public function canManageProperties(): bool
    {
        return $this->isAdmin() || $this->isPropertyUser();
    }

    public function canManageServices(): bool
    {
        return $this->isAdmin() || $this->isServiceProvider();
    }

    /**
     * Agent properties relation.
     */
    public function properties()
    {
        return $this->hasMany(Property::class, 'user_id');
    }
}
