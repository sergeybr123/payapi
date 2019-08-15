<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'payment_id',
        'email',
        'amount',
        'crc',
        'paid',
        'paid_at',
        'status',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];
}
