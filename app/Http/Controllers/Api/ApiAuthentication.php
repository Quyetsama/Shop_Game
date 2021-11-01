<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use JWTAuth;
use App\User;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Hash;
use Exception;

class ApiAuthentication extends Controller
{
    public function register(Request $request){
        try{
            $user = User::create([
                'name' => $request->get('name'),
                'username' => $request->get('username'),
                'email' => $request->get('email'),
                'password' => Hash::make($request->get('password'))
            ]);
        }
        catch(Exception $e){
            if ($e instanceof \Illuminate\Database\QueryException){
                return response()->json(['message' => 'Username or email already exists', 'status' => false]);
            }
            return response()->json(['message' => 'error']);
        }
        
        
        return response()->json([
            'status'=> true,
            'message'=> 'User created successfully',
            'data'=>$user
        ]);
    }


    /**
     * @var bool
     */
    public $loginAfterSignUp = true;

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try{
            $input = $request->only('username', 'password');
            $token = null;
    
            if (!$token = JWTAuth::attempt($input)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid Email or Password',
                ]);
            }
        }
        catch(Exception $e){
            return response()->json(['message' => 'error']);
        }

        return response()->json([
            'status' => true,
            'token' => $token,
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function logout(Request $request)
    {
        $this->validate($request, [
            'token' => 'required'
        ]);

        try {
            JWTAuth::invalidate($request->token);

            return response()->json([
                'status' => true,
                'message' => 'User logged out successfully'
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'status' => false,
                'message' => 'Sorry, the user cannot be logged out'
            ]);
        }
    }
}
