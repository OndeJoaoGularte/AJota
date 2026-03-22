<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Uma categoria tem várias Transações
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
