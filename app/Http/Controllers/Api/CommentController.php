<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Http\Requests\CommentRequest;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Validator;
use App\Models\Comment;
use App\Http\Requests\UpdateCommentRequest;
use App\Mail\CommentNotificationMail;
use App\Models\FriendRequest;
use App\Models\Post;
use App\Models\User;
use App\Service\DatabaseConnection;
use Illuminate\Support\Facades\Mail;

class CommentController extends Controller
{
    public function comment(CommentRequest $request) 
    {    
        $token = $request->bearerToken();
        try {
            $connection = new DatabaseConnection;
            $db = $connection->getdb();
            $decoded_data=JWT::decode($token, new Key("sumaila", 'HS256'));
            $input = $request->validated();
            $file_name = null;
            if (!empty($input['attachment'])) {    
                // upload Attachment
                $destinationPath = storage_path('\app\public\post\\'); 
                $input_type_aux = explode("/", $input['attachment']['mime']);
                $attachment_extention=$input_type_aux[1];
                $image_base64 = base64_decode($input['attachment']['data']);
                $file_name=uniqid() . '.'.$attachment_extention;
                $file = $destinationPath . $file_name;
                // saving in local storage
                file_put_contents($file, $image_base64);
                $input['attachment'] =  $file_name;    
            }
            $post_id = $input['post_id'];
            $post = $db->Post->findOne([
                '_id' => new \MongoDB\BSON\ObjectId("$post_id")
            ]);
            $user = $db->Users->findOne([
                '_id' => new \MongoDB\BSON\ObjectId("$post->user_id")
            ]);
            $input['post_id'] =  $input['post_id'];    
            $input['user_id'] = $decoded_data->data->id;
            //store your file into directory and db
            $comment = $db->Comment->insertOne($input); 
            $success_data['success']=$comment->isAcknowledged();
            $success_data['insert_id']=$comment->GetInsertedId()->__toString();
            //for generate link in URL
            $data['link']=url('api/GetPost/'.$post_id);
            $data['name'] = $user->name;
            $data['text'] = $post->text;
            $data['email'] = $user->email;
            Mail::to($data['email'])->send(new CommentNotificationMail($data));
            //for JSON response
            return response()->json([
                "success" => true,
                "message" => "Commented!",
                "file" => Null
            ]);
        }
        catch (\Exception $e) {            
            return response()->json(['error'=>$e->getMessage()], 500);                    
        }
    }
    //update Comments
    public function updateComment(UpdateCommentRequest $request){
        try{
        $connection = new DatabaseConnection;      
        $db = $connection->getdb(); 
        $input = $request->validated();
        $commentID = $input['comment_id'];
        $comment_exists = $db->Comment->findOne([
            '_id' => new \MongoDB\BSON\ObjectId("$commentID")
        ]);
        if(!empty($comment_exists)){
            if (!empty($input['attachment'])) {
                // upload Attachment
                $destinationPath = storage_path('\app\public\comments\\'); 
                $input_type_aux = explode("/", $input['attachment']['mime']);
                $attachment_extention=$input_type_aux[1];
                $image_base64 = base64_decode($input['attachment']['data']);
                $file_name=uniqid() . '.'.$attachment_extention;
                $file = $destinationPath . $file_name;
                // saving in local storage
                file_put_contents($file, $image_base64);
                $comment_exists->attachment = $file_name;
            }
            //store your file into directory and db
            $comment_exists->text = request('text');
            $db->Comment->updateOne([
                '_id' => new \MongoDB\BSON\ObjectId("$commentID") ],
                [ 
                    '$set' => $comment_exists
            ]);  
            return response()->json([
                "success" => true,
                "message" => "Comment Updated Successfully!"
            ]);
        }else{
            return response()->json([
                "success" => false,
                "message" => "Comment Does not Exist!"
            ]);
        }
    }
        catch (\Exception $e) {            
            return response()->json(['error'=>$e->getMessage()], 500);                    
        }
    }
        //Delete Comment
        public function DeleteComment($id){
            try{
            $user = new Comment();
            $user = Comment::find($id);
            if($user){
            $user->delete();
            return response()->json([
                "success" => true,
                "message" => "Comment Deleted Successfully!!",
            ]);
    }
        else{
            return response()->json([
                "success" => true,
                "message" => "Comment deost not exist",
                "data" => $user
            ]);
        }
    }
        catch (\Exception $e) {            
            return response()->json(['error'=>$e->getMessage()], 500);                    
        }
    }
        //Fetch Single Comment
        public function GetComment(Request $request,$id)
        {
            try{
            $request['id']=$id;
            $validator = Validator::make($request->all(),[ 
                'id' => 'exists:Comments,id',
                ]);
            if($validator->fails()) {          
                return response()->json(['error'=>$validator->errors()], 401);                        
            }
            $data=$validator->validated();
            $user_data=Comment::find($data['id']);
            return response()->json([
                "success" => true,
                "data" => $user_data
            ]);
        }
            catch (\Exception $e) {            
                return response()->json(['error'=>$e->getMessage()], 500);                    
            }
        } 
        //Fetch all Comments
        public function GetAllComment(Request $request)
        {
            try{
            $user_data=Comment::all();
            return response()->json([
                "success" => true,
                "data" => $user_data
            ]);
        }
            catch (\Exception $e) {            
                return response()->json(['error'=>$e->getMessage()], 500);                    
            }
        }
        
}
