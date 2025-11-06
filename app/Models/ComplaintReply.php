<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComplaintReply extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'complaint_id',
        'user_id',
        'estate_id',
        'message'
    ];

    public function estate()
    {
        return $this->belongsTo(Estate::class);
    }

    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

