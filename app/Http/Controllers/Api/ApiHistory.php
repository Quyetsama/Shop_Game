<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bill;
use App\Models\DetailBill;
use App\User;

class ApiHistory extends Controller
{
    public function purchaseHistory(Request $request){
        try{  
            $user = auth()->user();
            $bill = Bill::select('bills.id', 'products.name', 'bills.total_coin', 'detail_products.code_card', 'detail_products.account_game', 'detail_products.password_game', 'bills.created_at')
                        ->join('detail_bills', 'detail_bills.bill_id', '=', 'bills.id')
                        ->join('detail_products', 'detail_products.id', '=', 'detail_bills.product_id')
                        ->join('products', 'products.id', '=', 'detail_products.product_id')
                        ->where('user_id', '=', $user->id)->orderBy('bills.id', 'DESC')->get();
        }
        catch(Exception $e){
            if ($e instanceof \Illuminate\Database\QueryException){
                return response()->json(['message' => 'error', 'status' => false]);
            }else{
                return response()->json(['message' => 'error', 'status' => false]);
            }
        }  

        return response()->json(['message' => 'success', 'status' => true, 'profile' => $user, 'data' => $bill]);
    }
}
