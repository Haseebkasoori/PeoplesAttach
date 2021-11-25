<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\userController;

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

    //MIDDLEWARE Checking Reqeust, valid or not
// Route::middleware([JwtAuth::class])->group(function(){
Route::group(['middleware' => ['JwtAuth', 'FriendsOrNot']], function() {

    //CREATE FRIEND REQEUST
    Route::post('SendFriendRequest', [UserController::class, 'SendFriendRequest']);
});
