<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use App\Models\Category;
use App\Models\Product;
use App\Models\DetailProduct;
use App\Models\Bill;
use App\Models\DetailBill;
use App\User;

class ApiProducts extends Controller
{
    public function getCategory(Request $request){
        try{  
            $categories = Category::all();
        }
        catch(Exception $e){
            if ($e instanceof \Illuminate\Database\QueryException){
                return response()->json(['message' => 'error', 'status' => false]);
            }else{
                return response()->json(['message' => 'error', 'status' => false]);
            }
        }  

        return response()->json(['message' => 'success', 'status' => true, 'data' => $categories]);
    }

    public function getProduct(Request $request){
        try{  
            $products = Product::all();
        }
        catch(Exception $e){
            if ($e instanceof \Illuminate\Database\QueryException){
                return response()->json(['message' => 'error', 'status' => false]);
            }else{
                return response()->json(['message' => 'error', 'status' => false]);
            }
        }  

        return response()->json(['message' => 'success', 'status' => true, 'data' => $products]);
    }

    public function getProductByCategory(Request $request){
        try{  
            // $products = Product->join('category_product', 'products.id', '=', 'category_product.produc_id')->get();
            $products = Product::select('products.*')->leftJoin('category_product', 'category_product.product_id', '=', 'products.id')->where('category_id','=' , $request->get('category'))->get();
        }
        catch(Exception $e){
            if ($e instanceof \Illuminate\Database\QueryException){
                return response()->json(['message' => 'error', 'status' => false]);
            }else{
                return response()->json(['message' => 'error', 'status' => false]);
            }
        }  

        return response()->json(['message' => 'success', 'status' => true, 'data' => $products]);
    }

    public function payment(Request $request){
        // {
        //     'token': 'asdagdsfsd',
        //     'listproduct': [
        //         {'product_id': 1, 'quantity': 3},
        //         {'product_id': 2, 'quantity': 1},
        //         {'product_id': 3, 'quantity': 2},
        //     ]
        // }


        try{  
            // $out = new \Symfony\Component\Console\Output\ConsoleOutput();
            // $out->writeln(gettype($request->get('listproduct')));
            $listproduct = $request->get('listproduct');
            $products_id = array();
            for($i = 0; $i < count($listproduct); $i++){
                array_push($products_id, $listproduct[$i]['product_id']);
            }

            $thanhTien = 0;
            $products = Product::find($products_id);

            for($i = 0; $i < count($products); $i++){
                if($products[$i]['count'] < $listproduct[$i]['quantity']){
                    return response()->json(['message' => 'Hết sản phẩm ' . $products[$i]['name'], 'status' => false]);
                }
                $thanhTien += $products[$i]['price'] * $listproduct[$i]['quantity'];
            }

            $user = User::where('id', $request->get('user_id'))->first();
            if($user->coin >= $thanhTien){
                $user->coin -= $thanhTien;
                // $user->save();

                // $detailProduct = array();
                $arrID = array();
                for($i = 0; $i < count($listproduct); $i++){
                    $detailproduct = DetailProduct::select('detail_products.id')->take($listproduct[$i]['quantity'])
                    ->where('product_id', '=', $listproduct[$i]['product_id'])
                    ->where('sold', '=', false)->get();
                    // array_push($detailProduct, $detailproduct);
                    for($j = 0; $j < count($detailproduct); $j++){
                        array_push($arrID, $detailproduct[$j]['id']);
                    }
                    
                    $result = Product::where('id', '=', $listproduct[$i]['product_id'])->first();
                    $result->count -= $listproduct[$i]['quantity'];
                    $result->save();
                    $out = new \Symfony\Component\Console\Output\ConsoleOutput();
                    $out->writeln($result);
                }
                // create bill
                $bill = Bill::create([
                    'user_id' => $user->id
                ]);
                // update sold detail product
                DetailProduct::whereIn('id', $arrID)->update(['sold' => true]);
                // create detail bill
                for($i = 0; $i < count($arrID); $i++){
                    $detailBill = DetailBill::create([
                        'bill_id' => $bill->id,
                        'product_id' => $arrID[$i]
                    ]);
                }

                $user->save();

                // $detailProduct = DetailProduct::take(3)->where('product_id', '=', 2)->get();
                // $out = new \Symfony\Component\Console\Output\ConsoleOutput();
                // $out->writeln(count($detailProduct));
            }
            else{
                return response()->json(['message' => 'Số dư không đủ', 'status' => false]);
            }
            
        }
        catch(Exception $e){
            if ($e instanceof \Illuminate\Database\QueryException){
                return response()->json(['message' => $e, 'status' => false]);
            }else{
                return response()->json(['message' => $e, 'status' => false]);
            }
        }  

        return response()->json(['message' => 'success', 'status' => true, 'total coin' => $thanhTien]);
    }

    public function test(Request $request){
        try{  
            $products = DetailProduct::select('*')->take(3)->where('product_id', '=', 1)->where('sold', '=', false)->update(['sold' => true]);
        }
        catch(Exception $e){
            if ($e instanceof \Illuminate\Database\QueryException){
                return response()->json(['message' => 'error', 'status' => false]);
            }else{
                return response()->json(['message' => 'error', 'status' => false]);
            }
        }  

        return response()->json(['message' => 'success', 'status' => true, 'data' => $products]);
    }
}
