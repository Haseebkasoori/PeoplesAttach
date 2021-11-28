<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\FriendRequest;
use App\Models\User;
use Exception;

class FriendRequetPendingOrNot
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
            if(isset($request->user_data)){
                $pending_or_block_friend_reqeust=FriendRequest::where([
                                                    ['id',$request->friend_request_id],
                                                    ['reciever_id',$request->user_data->id],
                                                    ['status','Pending']
                                                ])->orWhere([
                                                ['id',$request->friend_request_id],
                                                ['reciever_id',$request->user_data->id],
                                                ['status','Block']
                                            ])->first();
                request()->merge(['pending_or_block_friend_reqeust'=>$pending_or_block_friend_reqeust]);
                return $next($request);
                if(!isset($pending_or_block_friend_reqeust))
                {
                    throw new Exception("You haven't any reqeust on this Id");
                }
            }else{
                throw new Exception("Session Expire!!");
            }

        }catch(\Exception $ex){
            info($ex->getMessage());
            $data['error']=$ex->getMessage();
            $data['message']="Someting went Worng";
            return response()->error($data,400);
        }
    }
}
