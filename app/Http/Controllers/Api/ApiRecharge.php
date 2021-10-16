<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Recharge;
use App\User;
use Carbon\Carbon;
use Exception;

class ApiRecharge extends Controller
{
    public function Recharge(Request $request){
        try{
            $recharge_code = Carbon::now()->timestamp . $request->get('user_id');

            // $out = new \Symfony\Component\Console\Output\ConsoleOutput();
            // $out->writeln($recharge_code . ' _ ' . $request->get('user_id'));
    
            $recharge = Recharge::create([
                'user_id' => $request->get('user_id'),
                'recharge_code' => $recharge_code,
                'coin' => $request->get('coin')
              ]);
        }
        catch(Exception $e){
            if ($e instanceof \Illuminate\Database\QueryException){
                return response()->json(['message' => 'error'], 400);
            }else{
                return response()->json(['message' => 'error'], 500);
            }
        }
        

        return response()->json(['message' => 'success', 'code' => $recharge_code], 201);
    }

    public function confirmRecharge(Request $request){
        try{  
            // $out = new \Symfony\Component\Console\Output\ConsoleOutput();
            // $out->writeln($request->get('code') . ' _ ' . $request->get('user_id'));

            // $confirm_recharge = Recharge::where('recharge_code', $request->get('code'))->update(['status' => true]);

            $recharge = Recharge::where('recharge_code', $request->get('code'))->first();
            if($recharge->status == false){
                $recharge->status = true;

                $recharge->save();
                $user = User::where('id', $recharge->user_id)->first();
                $user->coin += $recharge->coin;
                $user->save();
            }
            
        }
        catch(Exception $e){
            if ($e instanceof \Illuminate\Database\QueryException){
                return response()->json(['message' => 'error'], 400);
            }else{
                return response()->json(['message' => 'error'], 500);
            }
        }
        

        return response()->json(['message' => 'success'], 200);
    }
}
