<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $connection = 'getchat';

    protected $table = 'payments';

    protected $fillable = [
        'answer_id'
    ];

    protected $casts = [
        'data' => 'array',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
