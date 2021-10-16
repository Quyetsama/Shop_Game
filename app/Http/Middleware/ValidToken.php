<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use App\User;
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class ValidToken extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $role, $controller)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if($user->role->name !== $role){
                return response()->json(['status' => 'Not permisstion']);
            }

            $request->attributes->add(['user_id' => $user->id]);

            if($controller == 'recharge'){
                $request->attributes->add(['coin' => $request->coin]);
            }
            else if($controller == 'confirm_recharge'){
                $request->attributes->add(['code' => $request->code]);
            }
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return response()->json(['status' => 'Token is Invalid']);
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                return response()->json(['status' => 'Token is Expired']);
            }else{
                return response()->json(['status' => 'Error']);
            }
        }
        return $next($request);
    }
}
