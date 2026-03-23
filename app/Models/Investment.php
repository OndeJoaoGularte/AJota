<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Investment extends Model
{
    protected $fillable = ['description', 'amount', 'date', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}