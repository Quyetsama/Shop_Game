<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailProduct extends Model
{
    protected $table = 'detail_products';

    protected $hidden = [
        'sold', 'created_at', 'updated_at',
    ];

    public function product(){
        return $this->belongsTo('App\Models\Product');
    }
}
