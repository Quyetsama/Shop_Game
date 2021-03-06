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
use App\Models\Test;


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

    public function trendingProducts(Request $request){
        try{  
            $products = DetailProduct::join('products', 'products.id', '=', 'detail_products.product_id')
                                    ->selectRaw('product_id, name, image, price, discount, count(sold) as total')
                                    ->take(6)
                                    ->where('sold', '=', true)
                                    ->groupBy('product_id', 'name', 'image', 'price', 'discount')
                                    ->orderBy('total', 'DESC')
                                    ->get();
        }
        catch(Exception $e){
            if ($e instanceof \Illuminate\Database\QueryException){
                return response()->json(['message' => $e, 'status' => false]);
            }else{
                return response()->json(['message' => $e, 'status' => false]);
            }
        }  

        return response()->json(['message' => 'success', 'status' => true, 'data' => $products]);
    }

    public function payment(Request $request){
        
        // {
        //     'token': '123',
        //     'product_id': 1
        // }

        try{  
            $user = auth()->user();
            // $user->coin += 1;
            // $user->save();
            // T??m s??? l?????ng s???n ph???m
            $product = Product::where('id', '=', $request->get('product_id'))->first();
            if($product->count < 1){
                return response()->json(['message' => 'S???n ph???m ' . $product->name . ' ???? b??n h???t!', 'status' => false]);
            }
            else if($user->coin >= ($product->price * (100 - $product->discount)) / 100){
                // Tr??? ti???n user
                $user->coin -= ($product->price * (100 - $product->discount)) / 100;

                // T??m th??ng tin chi ti???t s???n ph???m
                $detailproduct = DetailProduct::where('product_id', '=', $request->get('product_id'))
                                ->where('sold', '=', false)->first();
                
                // S???a th??nh ???? b??n
                $detailproduct->sold = true;

                // Gi???m s??? l?????ng trong b???ng s???n ph???m
                $product->count -= 1;

                // T???o h??a ????n
                $bill = Bill::create([
                    'user_id' => $user->id,
                    'total_coin' => ($product->price * (100 - $product->discount)) / 100
                ]);

                // Th??m th??ng tin v??o h??a ????n
                $detailBill = DetailBill::create([
                    'bill_id' => $bill->id,
                    'product_id' => $detailproduct->id
                ]);

                $user->save();
                $product->save();
                $detailproduct->save();

                return response()->json(['message' => 'success', 'status' => true, 'coin' => ($product->price * (100 - $product->discount)) / 100, 'data' => $detailproduct]);
            }
            else{
                return response()->json(['message' => 'S??? d?? kh??ng ????? vui l??ng n???p th??m!', 'status' => false]);
            }

        }
        catch(Exception $e){
            if ($e instanceof \Illuminate\Database\QueryException){
                return response()->json(['message' => $e, 'status' => false]);
            }else{
                return response()->json(['message' => $e, 'status' => false]);
            }
        }  
    }

    public function searchProduct(Request $request){
        try{  
            $products = Product::where('name', 'like', '%' . $request->get('product') . '%')->get();
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

    public function paymentCart(Request $request){
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
                    return response()->json(['message' => 'H???t s???n ph???m ' . $products[$i]['name'], 'status' => false]);
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
                        // $detailproduct[$i]->product->count -= 1;
                        // $detailproduct[$i]->product->save();
                    }
                    
                    $result = Product::where('id', '=', $listproduct[$i]['product_id'])->first();
                    $result->count -= $listproduct[$i]['quantity'];
                    $result->save();
                    // $out = new \Symfony\Component\Console\Output\ConsoleOutput();
                    // $out->writeln($result);
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
                return response()->json(['message' => 'S??? d?? kh??ng ?????', 'status' => false]);
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
            $products = Test::all();
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
