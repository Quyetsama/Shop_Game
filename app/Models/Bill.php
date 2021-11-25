<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    protected $fillable = [
        'user_id', 'total_coin'
    ];

    public function detailbill(){
        return $this->hasMany('App\Models\DetailBill');
    }
}
