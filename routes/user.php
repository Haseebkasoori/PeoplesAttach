<?php

use App\Http\Controllers\API\RegistrationController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EmailVarified;
use App\Http\Controllers\API\ForgotPasswordController;
use App\Http\Controllers\API\LoginController;
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

    //REGISTER
    Route::post('register', [RegistrationController::class, 'register']);
    
    //MIDDLEWARE
    Route::middleware([EmailVarified::class])->group(function(){
    //LOGIN
    Route::post('UserLogin', [LoginController::class, 'login']);
    //FORGOT PASSWORD
    Route::get('/forgotPassword', [ForgotPasswordController::class, 'forgotPassword']);
    });

    //EMAIL VERIFY
    Route::get('emailConfirmation/{email}/{token}', [RegistrationController::class, 'verifyingEmail']);

    //MIDDLEWARE 
    Route::middleware([JwtAuth::class])->group(function(){

    //CRUD Users SECTION
    //Get specific user
    Route::get('GetUser/{id}', [UserController::class, 'GetUser']);
    //Get All user
    Route::get('GetUser', [UserController::class, 'GetAllUsers']);
    //Update
    Route::put('updateUser/{id}', [userController::class, 'UpdateUser']);
    //Delete 
    Route::delete('deleteUser/{id}', [userController::class, 'DeleteUser']);
    //Search User 
    Route::get('search/{name}', [userController::class, 'SearchUser']);
        
    //LOGOUT
    Route::get('logout', [LoginController::class, 'logout']);
});
