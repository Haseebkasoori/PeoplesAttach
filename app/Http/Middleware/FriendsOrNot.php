<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\FriendRequest;
use App\Models\Posts;
use App\Models\User;
use Exception;

class FriendsOrNot
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
            $user_data=request()->user_data;
            if (isset($request->post_id)) {
                $post=Posts::with('User','Comments')->where('id',$request->post_id)->first();
                // chekcing if they are friends or not
                $friends_or_not=FriendRequest::where([['sender_id',$post->user_id],['reciever_id',$user_data->id],['status','Approved']])
                ->orwhere([['sender_id',$user_data->id],['reciever_id',$post->user_id],['status','Approved']])
                ->first();


                if (!empty($friends_or_not->id)) {
                    $request->merge(['single_post_data'=>$post]);
                    return $next($request);
                }elseif($post->user_id==$user_data->id){
                    $request->merge(['single_post_data'=>$post]);
                    return $next($request);
                }else{

                    throw new Exception("All posts are private");

                }

            }else{

                $friends_or_not=FriendRequest::where([['sender_id',$request->user_id],['reciever_id',$user_data->id],['status','Approved']])
                                        ->orwhere([['sender_id',$user_data->id],['reciever_id',$request->user_id],['status','Approved']])
                                        ->first();
                if(!isset($friends_or_not))
                {
                    throw new Exception("All posts are private");
                }

                return $next($request);
            }


        }catch(\Exception $ex){
            info($ex->getMessage());
            $data['error']=$ex->getMessage();
            $data['message']="Someting went Worng";
            return response()->error($data,400);
        }
    }
}
