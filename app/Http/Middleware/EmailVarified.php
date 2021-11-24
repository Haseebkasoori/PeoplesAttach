<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Service\DatabaseConnection;
use App\Services\DatabaseConnection as ServicesDatabaseConnection;
use Closure;
use Illuminate\Http\Request;

class EmailVarified
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
        try{
            $connection = new ServicesDatabaseConnection;
            $database = $connection->getDatabase();
            $user_data=$database->Users->findOne([ 'email' => $request->email ]);
            if(!empty($user_data)){
                if ($user_data->email_verified_at===null) {

                    $data['error']="You didn't confirm your email yet!!";
                    $data['message']="Someting went Worng";
                    return response()->error($data,404);
                }else{
                    return $next($request);
                }
            }else{
                $data['error']="Email not Register";
                $data['message']="Someting went Worng";
                return response()->error($data,404);
            }
        }catch(\Exception $ex){

            $data['error']=$ex->getMessage();
            $data['message']="Someting went Worng";
            return response()->error($data,404);
        }
    }

}
