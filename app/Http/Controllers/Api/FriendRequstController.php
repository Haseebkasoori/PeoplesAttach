<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller as BaseController;
use App\Http\Requests\AddFriendRequest;
use App\Http\Requests\UpdateFriendRequest;
use App\Http\Resources\UserResource;
use App\Jobs\FriendRequestEmailJob;
use App\Jobs\FriendUpateEmailJob;
use App\Models\FriendRequest;
use App\Models\User;


class FriendRequstController extends BaseController
{
    // Create friend request
    public function SendFriendRequest(AddFriendRequest $request){
        try {
            $friendRequest = new FriendRequest();
            $friendRequest->sender_id = request()->user_data->id;
            $friendRequest->reciever_id = $request->reciever_id;
            $friendRequest->save();
            $receiver_user = User::find($request->reciever_id);
            // dd($receiver_user);
            $email_data['receiver_user_name'] = $receiver_user->name;
            $email_data['sender_user_name'] = request()->user_data->name;
            $email_data['link']=url('friendrequest/GetAllFriendRequest/');
            // send email
            dispatch(new FriendRequestEmailJob($email_data, $receiver_user->email));

            $data['data']= new UserResource(request()->user_data);
            $data['message']='Friend Reqeust Sended!!';
            return response()->success($data,200);
        }
        catch (\Exception $e) {
            info($e->getMessage());
            $data['error']=null;
            $data['message']="Someting went Worng";
            return response()->error($data, 500);
        }
    }

    // Create friend request
    public function UpdateFriendRequest(UpdateFriendRequest $request){
        try {
            $friendRequest=$request->pending_or_block_friend_reqeust;
            // dd($friendRequest);
            $friendRequest->status = $request->status;
            $friendRequest->update();
            $sender_user = User::find($friendRequest->sender_id);

            $email_data['receiver_user_name'] = request()->user_data->user_name;
            $email_data['sender_user_name'] = $sender_user->user_name;

            // send email
            dispatch(new FriendUpateEmailJob($email_data));

            $data['message']='Friend Reqeust Updated!!';
            $data['data']= new UserResource(request()->user_data);
            return response()->success($data,200);
        }
        catch (\Exception $e) {
            info($e->getMessage());
            $data['error']=null;
            $data['message']="Someting went Worng";
            return response()->error($data, 500);
        }
    }



}
