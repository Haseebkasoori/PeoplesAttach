<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\FriendRequest;
use App\Models\User;
use App\Services\JwtAuthentication;

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
        // try{
            $user_data=$request->user_data;
            // $JWT_decoced_data=$request->decoded_data;
            $already_sended=FriendRequest::where([['sender_id',$user_data->id],['reciever_id',$request->reciever_id],['status','Pending']])->first();
            $already_recieve=FriendRequest::where([['reciever_id',$user_data->id],['sender_id',$request->reciever_id],['status','Pending']])->first();
            $already_block=FriendRequest::where([['reciever_id',$user_data->id],['sender_id',$request->reciever_id],['status','Block']])
                                            ->orwhere([['sender_id',$user_data->id],['reciever_id',$request->reciever_id],['status','Block']])->first();
            if($user_data->id==$request->reciever_id){

                $data['error']="You can not send request to your Self";
                $data['message']="Someting went Worng";
                return response()->error($data,404);

            }elseif (!empty($already_sended)) {

                $data['error']="Already Friend Request Send Waiting for Response";
                $data['message']="Someting went Worng";
                return response()->error($data,404);

            }elseif (!empty($already_recieve)) {

                $data['error']="Already have recieved reqeust, check and responce";
                $data['message']="Someting went Worng";
                return response()->error($data,404);

            }elseif (!empty($already_recieve)) {

                $data['error']="Already have recieved reqeust, check and responce";
                $data['message']="Someting went Worng";
                return response()->error($data,404);

            }elseif (!empty($$already_block)) {

                $data['error']="Not Allowed, Becasue User Already Block You";
                $data['message']="Someting went Worng";
                return response()->error($data,404);

            }

        //     // check if user data exist
        //     if (!isset($user_data->jwt_token)) {
        //         $data['error']="LogOut, Please Login";
        //         $data['message']="Someting went Worng";
        //         return response()->error($data,404);
        //     }else{
        //         request()->merge(['user_data'=>$user_data,'decoded_data'=>$JWT_decoced_data]);

        //     }
        // }catch(\Exception $ex){
        //     $data['error']=$ex->getMessage();
        //     $data['message']="Someting went Worng";
        //     return response()->error($data,404);
        // }
    }
}
