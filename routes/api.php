<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('register', 'Api\ApiAuthentication@register');  // name, email, username, password
Route::post('login', 'Api\ApiAuthentication@login');    // username, password
Route::post('loginadmin', 'Api\ApiAuthentication@loginAdmin');    // username, password
Route::post('recharge', 'Api\ApiRecharge@Recharge')->middleware('validtoken:user, recharge');   // token(user), coin
Route::post('confirmrecharge', 'Api\ApiRecharge@confirmRecharge')->middleware('validtoken:admin, confirm_recharge');    // token(admin), code
Route::post('checktoken', 'Api\ApiAuthentication@checkToken');
Route::post('checktokenadmin', 'Api\ApiAuthentication@checkTokenAdmin');
Route::get('getrecharge', 'Api\ApiRecharge@getRecharge')->middleware('validtoken:admin, ""');


Route::group(['middleware' => 'auth.jwt'], function () {
    Route::get('logout', 'Api\ApiAuthentication@logout');
});