<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Http\Requests\SendFriendRequest;
use App\Http\Requests\UpdateFriendRequest;
use App\Mail\FriendRequestMail;
use App\Models\FriendRequest;
use App\Models\User;
use App\Service\DatabaseConnection;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Mail;

class FriendRequestController extends Controller
{
    //Create friend request
    public function SendFriendRequest(SendFriendRequest $request){
        $token = $request->bearerToken(); 
        try {
            $connection = new DatabaseConnection;
            $db = $connection->getdb();
            $decoded_data=JWT::decode($token, new Key("sumaila", 'HS256'));
            $input = $request->validated();
            $input['sender'] = $decoded_data->data->id;
            $friendRequest = $db->Friend_Request->insertOne($input);
            $receiver_user = $db->Users->findOne([
                '_id' => new \MongoDB\BSON\ObjectId($input['reciever'])
            ]);
            $data['receiver_user_name'] = $receiver_user->name;
            $data['sender_user_name'] = $decoded_data->data->name;
            $success_data['success']=$friendRequest->isAcknowledged();
            $data['link']=url('friendRequest/GetAllFriendRequest/'.$friendRequest->GetInsertedId()->__toString());
            Mail::to($receiver_user->email)->send(new FriendRequestMail($data));
            $success['success']=$friendRequest->isAcknowledged();
            $success['insert_id']=$friendRequest->GetInsertedId()->__toString();
            return response()->json(['success'=>'Request sent successfully!'], 200);  
        }
        catch (\Exception $e) {
            return response()->json(['error'=>$e->getMessage()], 500);                    
            }
    }
    //update friend Request
    public function UpdateFriendRequest(UpdateFriendRequest $request){
    try {
        $decoded = JWT::decode($request->bearerToken(), new Key("sumaila", 'HS256'));
        $connection = new DatabaseConnection;      
        $db = $connection->getdb(); 
        $input = $request->validated();
        $friendRequest = $db->Friend_Request->findOne([
            'sender' => $input['sender']
        ]);
            $friendRequest->status = $input['status'];
            $db->Friend_Request->updateOne([
                'sender' => $input['sender'] ],
                [ 
                    '$set' => $friendRequest
                ]); 
            return response()->json(['success'=>'Request Updated successfully!'], 200);  
        }
    catch (\Exception $e) {
        return response()->json(['error'=>$e->getMessage()], 500);                    
    }
    }
    //Remove Friend Request
    public function RemoveFriendRequest($id){
    try{
        $connection = new DatabaseConnection;      
        $db = $connection->getdb(); 
        
        $Friend = $db->Friend_Request->findOne([
            '_id' => new \MongoDB\BSON\ObjectId("$id")
        ]);
        if($Friend){
            $db->Friend_Request->deleteOne($Friend);
            return response()->json([
                "success" => true,
                "message" => "Request Remove Successfully!!"
            ]);
        }
        else{
            return response()->json([
                "success" => true,
                "message" => "Request no longer exist"
            ]);
        }
    }
    catch (\Exception $e) {
        return response()->json(['error'=>$e->getMessage()], 500);                    
    }
    
    }
    //Get All Sended Friend Request
    public function GetAllSendFriendRequest(){
        try{
            $connection = new DatabaseConnection;      
            $db = $connection->getdb(); 
            $token=request()->bearerToken();   
            $secret_key="sumaila";     
            $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
            $user_data = $db->Friend_Request->find(['sender' => $decoded->data->id])->toArray();
            if(!empty($user_data)){
                return response()->json([
                    "success" => true,
                    "message" => "New Friend Request!",
                    "user_data"=>$user_data
                ]);
            }
            else{
                return response()->json([
                    "success" => true,
                    "message" => "There is No New friend Request"
                ]);
            }
        }
        catch (\Exception $e) {
            return response()->json(['error'=>$e->getMessage()], 500);                    
        }
    }
    //Get All Recieved Friend Request
    public function GetAllRecievedFriendRequest(){
        try{
            $connection = new DatabaseConnection;      
            $db = $connection->getdb(); 
            $token=request()->bearerToken();   
            $secret_key="sumaila";     
            $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
            $user_data = $db->Friend_Request->find(['reciever' => $decoded->data->id])->toArray();
            if(!empty($user_data)){
                return response()->json([
                    "success" => true,
                    "message" => "New Friend Request!",
                    "user_data"=>$user_data
                ]);
            }
            else{
                return response()->json([
                    "success" => true,
                    "message" => "There is No New friend Request"
                ]);
            }
        }
        catch (\Exception $e) {
            return response()->json(['error'=>$e->getMessage()], 500);                    
        }
    }
}
