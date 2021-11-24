<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\FriendRequestController;

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
    //MIDDLEWARE 
    Route::middleware([JwtAuth::class])->group(function(){
    //FRIENDS SECTION
    //Send Friend Request
    Route::post('SendFriendRequest', [FriendRequestController::class, 'SendFriendRequest']);
    //update Frien d Request
    Route::put('updateFriendRequest', [FriendRequestController::class, 'UpdateFriendRequest']);
    //Delete Friend Request
    Route::delete('RemoveFriendRequest/{id}', [FriendRequestController::class, 'RemoveFriendRequest']);
    
});
//Get Friend Request
Route::get('GetAllSendFriendRequest', [FriendRequestController::class, 'GetAllSendFriendRequest']);
Route::get('GetAllRecievedFriendRequest', [FriendRequestController::class, 'GetAllRecievedFriendRequest']);
    
