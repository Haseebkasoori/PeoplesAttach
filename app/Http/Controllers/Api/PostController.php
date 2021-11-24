<?php
   
namespace App\Http\Controllers\Api;
   
use App\Http\Controllers\Controller as BaseController;
use Illuminate\Support\Facades\Validator;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\Posts;
// use Mail;


class PostController extends BaseController 
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function CreatePost(Request $request)
    {
        $request_data=$request->json()->all();
        $request_data['id']=$request_data['user_id'];

        $validator = Validator::make($request_data, [
            'text' => 'string|max:255',
            'id' => 'required|exists:users',
            'attachment' => 'array',
            'visibility' => 'string|in:public,private',
        ]);
        
        if($validator->fails()){
            $data['error']=$validator->errors();
            $data['message']="Someting went Worng";
            return response()->error($data,404);
        }else{
            $data=$validator->validated();
            
            // converting base64 decoded image to simple image
            $file_name=null;    
            if (!empty($data['attachment'])) {
            
                // upload Attachment
                $destinationPath = storage_path('api_data\users\\');
                $data_type_aux = explode("/", $data['attachment']['mime']);
                $attachment_type=$data_type_aux[0];
                $attachment_extention=$data_type_aux[1];
                $image_base64 = base64_decode($data['attachment']['data']);
                $file_name=$data['user_name'].uniqid() . '.'.$attachment_extention;
                $file = $destinationPath . $file_name;
                // saving in local storage
                file_put_contents($file, $image_base64);
            }

            $posts_obj = new Posts();
            $posts_obj->text=$data['text'];
            $posts_obj->user_id=$data['id'];
            $posts_obj->post_id=uniqid();
            $posts_obj->visibility=$data['visibility'];
            $posts_obj->attachment=$file_name;
            try{
                // save post in db
                $posts_obj->save();

                // return response
                $data['data']=Null;
                $data['message']='Post Created Successfully';
                return response()->success($data,200);

            }catch(\Exception $ex){
                $data['error']=$ex->getMessage();
                $data['message']="Someting went Worng";
                return response()->error($data,404);
            }            
        }
    }
    public function DeletePost(Request $request){



    }

    
}