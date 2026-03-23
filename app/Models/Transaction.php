<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'description', 'amount', 'type', 'date', 'category_id', 
        'credit_card_id', 'installment_number', 'total_installments', 'is_paid'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function creditCard()
    {
        return $this->belongsTo(CreditCard::class);
    }
}