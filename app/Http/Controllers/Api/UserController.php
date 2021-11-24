<?php

namespace App\Http\Controllers\API;
//use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller; 
use Illuminate\Http\Request;
use App\Service\DatabaseConnection;
class UserController extends Controller
{
public $successStatus = 200;

    //update users data
    public function UpdateUser(Request $request, $id){
    try{
        $connection = new DatabaseConnection;      
        $db = $connection->getdb(); 

        $user=$db->Users->findOne([
            '_id' => new \MongoDB\BSON\ObjectId("$id")
        ]);
    $user->name = $request->input('name');
    $user->email = $request->input('email');
    $user->age = $request->input('age');
    $user->date_of_birth = $request->input('date_of_birth');
    $file_name = null;
    if (!empty($request['profile_image'])) {
        // converting base64 decoded image to simple image
        // upload path
        $destinationPath = storage_path('\app\public\users\\'); 
        $image_parts = explode(";base64,", $request['profile_image']);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);
        $file_name=uniqid() . '.'.$image_type;
        $file = $destinationPath . $file_name;
        // saving in local storage
        file_put_contents($file, $image_base64);  
    
    }
    $db->Users->updateOne([
        '_id' => $user->_id ],
        [ 
            '$set' => $user
        ]); 
    return response()->json([
        "success" => true,
        "message" => "Updated Successfully!",
        "data" => $user
    ]);
}
    catch (\Exception $e) {
        return response()->json(['error'=>$e->getMessage()], 500);                    
    }
}

    //Delete post
    public function DeleteUser($id){
    try{
        $connection = new DatabaseConnection;      
        $db = $connection->getdb(); 

        $user=$db->Users->findOne([
            '_id' => new \MongoDB\BSON\ObjectId("$id")
        ]);
        if($user){
            $db->Users->deleteOne($user);
        return response()->json([
            "success" => true,
            "message" => "User Deleted Successfully!!",
            "data" => $user
        ]);
    }

    else{
        return response()->json([
            "success" => true,
            "message" => "User not exist",
            "data" => $user
        ]);
    }
}
    catch (\Exception $e) {
        return response()->json(['error'=>$e->getMessage()], 500);                    
    }
}
    //Searh User
    public function SearchUser(Request $request, $name)
    {
        try{
        $connection = new DatabaseConnection;      
        $db = $connection->getdb();
        $user = $db->Users->find([
            "name" => new \MongoDB\BSON\Regex("$name")
        ])->toArray();
            return response()->json($user, $this-> successStatus); 
    }
        catch (\Exception $e) {
            return response()->json(['error'=>$e->getMessage()], 500);                    
        }
    }
    //Fetch Single user data
    public function GetUser(Request $request,$id)
    {
        try{
        $connection = new DatabaseConnection;      
        $db = $connection->getdb(); 

        $user=$db->Users->findOne([
            '_id' => new \MongoDB\BSON\ObjectId("$id")
        ]);
        if($user){
            return response()->json([
                "success" => true,
                "data" => $user
            ]);
        }
        else{
            return response()->json(['error'=>'Id does not exists'], 401);
        }
    }
        catch (\Exception $e) {
            return response()->json(['error'=>$e->getMessage()], 500);                    
        }
    }

    //Fetch all users data
    public function GetAllUsers(Request $request)
    {
    try{
        $connection = new DatabaseConnection;      
        $db = $connection->getdb(); 
        $user_data = $db->Users->find()->toArray();
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