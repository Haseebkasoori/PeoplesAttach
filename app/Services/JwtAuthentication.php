<?php

namespace App\Services;

use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;


class JwtAuthentication
{
    /**
     * Create Jwt Token
     *
     * @return Token
     */
    public static function createJwtToken($user_data)
    {
        unset($user_data['email_varified_token']);
        unset($user_data['password']);
        unset($user_data['remember_token']);
        unset($user_data['created_at']);
        unset($user_data['deleted_at']);

        $secret_key="";
        $payload_info= array(
        "iss" => "localhost",
        "iat" => time(),
        "nbf" => time()+10,
        "exp" => time()+1800,
        "aud" => "People Attach",
        "data" =>$user_data
        );
        try {

            $Auth_key = JWT::encode($payload_info,config('Jwt.KEY'));

            return array('token'=>$Auth_key);

        } catch (\Exception $e) {

        return array('error'=>$e->getMessage());

        }
    }
    /**
     * Verify Token
     *
     * @return Varified token or exception error
     */
    public static function varifyToken($token)
    {
        $secret_key="P0551BL3";

        try{

            $decoded = JWT::decode($token, new Key(config('Jwt.KEY'), 'HS256'));
            return $decoded;

        }catch(\Exception $ex){
            $data['error']=$ex->getMessage();
            $data['message']="Someting went Worng";
            return response()->error($data,404);
        }
    }

}
