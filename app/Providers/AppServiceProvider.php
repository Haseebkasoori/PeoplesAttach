<?php

namespace App\Providers;

use App\Services\DatabaseConnection;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Response::macro('success',function($data,$status_code){
            http_response_code($status_code);
            return response()->json([
                'success' => true,
                'message' => $data['message'],
                'data'    => $data['data'],
            ],$status_code);

        });

        Response::macro('error',function($data, $status_code){
            http_response_code($status_code);
            return response()->json([
                'success' => false,
                'message' => $data['message'],
                'error'   => $data['error'],
            ],$status_code);

        });

        Validator::extend('unique_data',
        function($attribute, $value, $parameters, $validator){
            $connection = new DatabaseConnection;
            $collection_name=$parameters[0];
            $key_name=$parameters[1];
            $database = $connection->getDatabase();
            $user_exist=$database->$collection_name->findOne(["$key_name"=> $value]);
            if(empty($user_exist)){
                return true;
            }
            return false;
        },"The :attribute has already been taken!!");

        Validator::extend('exist_data',
        function($attribute, $value, $parameters, $validator){
            $connection = new DatabaseConnection;
            $collection_name=$parameters[0];
            $key_name=$parameters[1];
            $database = $connection->getDatabase();
            $user_exist=$database->$collection_name->findOne(["$key_name"=> $value]);
            if(empty($user_exist)){
                return false;
            }
            return true;
        },"The selected :attribute is invalid!!");

        Validator::extend('password_check',
        function($attribute, $value, $parameters, $validator){
            $connection = new DatabaseConnection;
            $collection_name=$parameters[0];
            $email=request()->email;
            $database = $connection->getDatabase();
            $user_exist=$database->$collection_name->findOne(["email"=> $email]);
            dd($user_exist);
            if(empty($user_exist)){
                return false;
            }
            return true;
        },"The selected :attribute is invalid!!");


    }
}
