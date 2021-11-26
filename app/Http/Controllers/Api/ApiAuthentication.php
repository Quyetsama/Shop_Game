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
                    'message' => 'Invalid Username or Password',
                ]);
            }
        }
        catch(Exception $e){
            return response()->json(['message' => 'error']);
        }

        $user = JWTAuth::user($token);

        // $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        // $out->writeln($user->role->name);

        return response()->json([
            'status' => true,
            'token' => $token,
            'profile' => $user,
        ]);
    }

    public function loginAdmin(Request $request)
    {
        try{
            $input = $request->only('username', 'password');
            $token = null;
    
            if (!$token = JWTAuth::attempt($input)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid Username or Password',
                ]);
            }
        }
        catch(Exception $e){
            return response()->json(['message' => 'error']);
        }

        $user = JWTAuth::user($token);

        if($user->role->name != 'admin'){
            return response()->json([
                'status' => false,
                'message' => 'Not Permission',
            ]);
        }

        return response()->json([
            'status' => true,
            'token' => $token,
            'profile' => $user,
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

    public function changePassword(Request $request){
        try{  
            $user = auth()->user();
            if(Hash::check($request->get('current_password'), $user->password)){
                $user->password = Hash::make($request->get('new_password'));
                $user->save();
                return response()->json(['message' => 'Đổi mật khẩu thành công', 'status' => true]);
            }
            else{
                return response()->json(['message' => 'Mật khẩu hiện tại không chính xác', 'status' => false]);
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

    public function checkToken(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate(); 
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return response()->json(['status' => false, 'message' => 'Token is Invalid']);
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                return response()->json(['status' => false, 'message' => 'Token is Expired']);
            }else{
                return response()->json(['status' => false, 'message' => 'Error']);
            }
        }
        return response()->json(['status' => true, 'profile' => $user]);
    }

    public function checkTokenAdmin(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate(); 
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return response()->json(['status' => false, 'message' => 'Token is Invalid']);
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                return response()->json(['status' => false, 'message' => 'Token is Expired']);
            }else{
                return response()->json(['status' => false, 'message' => 'Error']);
            }
        }

        if($user->role->name != 'admin'){
            return response()->json([
                'status' => false,
                'message' => 'Not Permission',
            ]);
        }

        return response()->json(['status' => true, 'profile' => $user]);
    }
}
