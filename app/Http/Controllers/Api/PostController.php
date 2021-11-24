<?php

namespace App\Http\Controllers\API;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller; 
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Requests\PostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Service\DatabaseConnection;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    //file uploading
    public function upload(PostRequest $request) 
    { 
        $token = $request->bearerToken(); 
        try {
            $connection = new DatabaseConnection;
            $db = $connection->getdb();
            $decoded_data=JWT::decode($token, new Key("sumaila", 'HS256'));
            $input = $request->validated();
            $file_name=null;  
            // converting base64 decoded image to simple image if exist
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
            }
            $input['attachment']=$file_name;
            $input['user_id']=$decoded_data->data->id;
            $post = $db->Post->insertOne($input); 
                //store your file into directory and db
            $success_data['success']=$post->isAcknowledged();
            $success_data['insert_id']=$post->GetInsertedId()->__toString();
                return response()->json([
                    "data" => $success_data,
                    "message" => "Post successfully Created",
                ]);
                }
            
        catch (\Exception $e) {
        return response()->json(['error'=>$e->getMessage()], 500);                    
        }
    }

    //Get Single Post
    public function GetPost(Request $request,$id)
    {
        $token=request()->bearerToken();
        try{
            $validator = Validator::make(['id'=>$id],[ 
                'id' => 'exists:post,id',
                ]);
            $data=$validator->validated();      
            $decoded_data = JWT::decode($token, new Key("sumaila", 'HS256'));
            $post_data=Post::with('User','Comments')->find($data['id']);
            $friend_request = DB::select(DB::raw("select * from friend_request where (reciever='{$decoded_data->data->id}'AND sender='{$post_data->user_id}')OR (sender='{$decoded_data->data->id}'AND reciever='{$post_data->user_id}') AND status='Approved'"));
            
            //check status
            if(!empty($friend_request[0]->id) ){
                return response()->json([
                    "success" => true,
                    "data" => $post_data
                ]);
            }else{
                return response()->json([
                    "success" => true,
                    "message" => "Sorry u r not the user's friend"
                ]);
            }
        }catch (\Exception $e) {
            return response()->json(['error'=>$e->getMessage()], 500);                    
        }
        
    }
    //Fetch all Posts
    public function GetAllPosts(Request $request, $user_id)
    {
        try{
            $request['id']=$user_id;
            $token=request()->bearerToken();

            $validator = Validator::make($request->all(), [ 
                'id' => 'required|exists:users',
                ]);
            $data=$validator->validated();      
            $secret_key="sumaila";
            $decoded_data = JWT::decode($token, new Key($secret_key, 'HS256'));
            $friend_request = DB::select(DB::raw("select * from friend_request where(reciever='{$decoded_data->data->id}'AND sender='{$user_id}')OR (sender='{$decoded_data->data->id}'AND reciever='{$user_id}') AND status='Approved'"));
            $post_data=Post::with('User','Comments')->where('user_id',$data['id'])->get();
            //check status
            if(!empty($friend_request[0]->id)){
                return response()->json([
                    "success" => true,
                    "data" => $post_data
                ]);
            }else{
                return response()->json([
                    "success" => true,
                    "message" => "Sorry u r not the user's friend"
                ]);
            }
        }
        catch (\Exception $e) {
            return response()->json(['error'=>$e->getMessage()], 500);                    
        }
    }
     //update Post data
     public function UpdatePost(Request $reqeust)
     {
        try{
            $connection = new DatabaseConnection;      
            $db = $connection->getdb(); 
            $post_exist = $db->Post->findOne([
                '_id' => new \MongoDB\BSON\ObjectId("$reqeust->id")
            ]);
            $decoded_data=JWT::decode($reqeust->bearerToken(), new Key("sumaila", 'HS256'));
            if(!empty($post_exist->visibility)){    
                if(!empty($post_exist->user_id==$decoded_data->data->id)){    
                    $data=$reqeust->toArray();
                    if (!empty($data['attachment'])) {
                        // upload Attachment
                        $destinationPath = storage_path('\app\public\post\\'); 
                        $input_type_aux = explode("/", $data['attachment']['mime']);
                        $attachment_extention=$input_type_aux[1];
                        $image_base64 = base64_decode($data['attachment']['data']);
                        $file_name=uniqid() . '.'.$attachment_extention;
                        $file = $destinationPath . $file_name;
                        // saving in local storage
                        file_put_contents($file, $image_base64);
                        $data['attachment'] = $file_name;
                    }
                    $data['user_id']=$decoded_data->data->id;
                    //store your file into directory and db
                    $db->Post->updateOne([
                        '_id' => new \MongoDB\BSON\ObjectId("$reqeust->id") ],
                        [ 
                            '$set' => $data
                    ]);  
                    return response()->json([
                        "success" => true,
                        "message" => "Post Updated Successfully!"
                    ]);
                }else{
                    return response()->json([
                        "success" => false,
                        "message" => "You are not allowed to Update this Post!"
                    ]);
                }
            }else{
                return response()->json([
                    "success" => false,
                    "message" => "Post Not exist!"
                ]);
            }
        }catch (\Exception $e) {
           return response()->json(['error'=>$e->getMessage()], 500);                    
        }
   }
    
    //Delete post
    public function DeletePost($id){
        try{
            $connection = new DatabaseConnection;      
            $db = $connection->getdb(); 
            
            $post = $db->Post->findOne([
                '_id' => new \MongoDB\BSON\ObjectId("$id")
            ]);
        if($post){
            $db->Post->deleteOne($post);
        return response()->json([
            "success" => true,
            "message" => "Post Deleted Successfully!!"
        ]);
    }
        else{
            return response()->json([
                "success" => true,
                "message" => "Post not exist"
            ]);
        }
    }
        catch (\Exception $e) {
            return response()->json(['error'=>$e->getMessage()], 500);                    
        }
    }
}
