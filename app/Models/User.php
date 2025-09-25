<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'phone',
        'email',
        'password',
        'avatar',
        'usertype',
        'verification_token', 
        'verification_token_expires_at',
        'email_verified_at',
        'apartment_unit',
        'full_address',
        'status'
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'verification_token'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'verification_token_expires_at' => 'datetime',
    ];

    // New relationships for estate management
    public function visitorCodes()
    {
        return $this->hasMany(VisitorCode::class);
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function verifiedVisitorCodes()
    {
        return $this->hasMany(VisitorCode::class, 'verified_by');
    }
}
