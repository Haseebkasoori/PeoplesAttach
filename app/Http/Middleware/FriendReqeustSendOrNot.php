<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\FriendRequest;
use Exception;

class FriendReqeustSendOrNot
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
            $user_data=$request->user_data;
            // $JWT_decoced_data=$request->decoded_data;
            $all_friends=FriendRequest::all()->toArray();
            if($user_data->id==$request->reciever_id){

                throw new Exception("You can not send request to your Self");

            }

            foreach ($all_friends as $single =>$keys) {

                if(($keys['sender_id']==$user_data->id)And($keys['reciever_id']==$request->reciever_id) And($keys['status']=='Pending'))
                {
                    throw new Exception("Already Friend Request Send Waiting for Response");

                }elseif (($keys['sender_id']==$request->reciever_id)And($keys['reciever_id']==$user_data->id) And($keys['status']=='Pending')) {

                    throw new Exception("Already have recieved reqeust, check and responce");

                }elseif ((($keys['sender_id']==$request->reciever_id) And ($keys['reciever_id']==$user_data->id) And($keys['status']=='Block'))or(($keys['sender_id']==$user_data->id) And ($keys['reciever_id']==$request->reciever_id) And($keys['status']=='Block'))) {

                    throw new Exception("Not Allowed, Becasue User Already Block You");

                }elseif ((($keys['sender_id']==$request->reciever_id) And ($keys['reciever_id']==$user_data->id) And($keys['status']=='Approved'))or(($keys['sender_id']==$user_data->id) And ($keys['reciever_id']==$request->reciever_id) And($keys['status']=='Approved'))) {

                    throw new Exception("Not Allowed, Becasue User Already Friend With You");
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
