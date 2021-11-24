<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\User;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $token=request()->bearerToken();        
        $secret_key="P0551BL3";
        try{
            $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
            $user_data=User::where('jwt_token',$token)->first();
            if (!$user_data['jwt_token']) {
                $data['error']="LogOut, Please Login";
                $data['message']="Someting went Worng";
                return response()->error($data,404);
            }else{
                return $next($request);
            }
        }catch(\Exception $ex){
            $data['error']=$ex->getMessage();
            $data['message']="Someting went Worng with Bearer Token";
            return response()->error($data,404);
        }
    }
}
