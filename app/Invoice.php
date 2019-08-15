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

    public function makeSignature(string $mrh_login, string $mrh_pass1) {
        $phrase = "$mrh_login:$this->amount:$this->id:$mrh_pass1";
        // формирование подписи
        $crc  = md5($phrase);
        return $crc;
    }
}
