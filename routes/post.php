<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\PostController;

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

    //POSTS SECTION
    //Create Post
    Route::post('createPost', [PostController::class, 'upload']);
    //Get specific Post
    Route::get('GetPost/{id}', [PostController::class, 'GetPost']);
    //Get All Post
    Route::get('GetAllPost/{user_id}', [PostController::class, 'GetAllPosts']);
    //Delete Post
    Route::delete('delete/{id}', [PostController::class, 'DeletePost']);
    //Update Post
    Route::put('update', [PostController::class, 'UpdatePost']);
});
