<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VisitorCode extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'estate_id',
        'visitor_name',
        'phone_number',
        'destination',
        'number_of_visitors',
        'code',
        'expires_at',
        'additional_notes',
        'verified_by',
        'verified_at',
        'status',
        'time_in',
        'time_out'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'time_in' => 'datetime',
        'time_out' => 'datetime',
    ];

    public function estate()
    {
        return $this->belongsTo(Estate::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function isExpired()
    {
        return $this->expires_at < now();
    }

    public function isVerified()
    {
        return !is_null($this->verified_at);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where('expires_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }
}
