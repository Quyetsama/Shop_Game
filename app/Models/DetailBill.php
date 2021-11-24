<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailBill extends Model
{
    protected $fillable = [
        'bill_id', 'product_id', 'quantity', 'totalcoin',
    ];
}
