<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recharge extends Model
{
    protected $fillable = [
        'user_id', 'recharge_code', 'coin',
    ];

    public function user(){
        return $this->belongsTo('App\User');
    }
}
