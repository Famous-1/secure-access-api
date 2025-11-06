<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'address',
        'phone',
        'email',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

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

    public function announcements()
    {
        return $this->hasMany(Announcement::class);
    }
}
