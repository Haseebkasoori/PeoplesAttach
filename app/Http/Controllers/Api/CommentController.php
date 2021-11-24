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


class CommentController extends BaseController 
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function CreateComment(Request $request)
    {
        $request_data=$request->json()->all();
        $request_data['id']=$request_data['user_id'];
        $request_data['id']=$request_data['post_id'];

        $validator = Validator::make($request_data, [
            'text' => 'string|max:255',
            'id' => 'required|exists:users',
            'attachment' => 'string',
            'visibility' => 'string|in:public,private',
        ]);
        
        if($validator->fails()){
            $data['error']=$validator->errors();
            $data['message']="Someting went Worng";
            return response()->error($data,404);
        }else{
            $data=$validator->validated();
            
            $file_name=null;    
            if (!empty($data['attachment'])) {

                // converting base64 decoded image to simple image

                // upload path
                $destinationPath = public_path('\api\posts\\'); 
                $image_parts = explode(";base64,", $data['attachment']);
                $image_type_aux = explode("image/", $image_parts[0]);
                $image_type = $image_type_aux[1];
                $image_base64 = base64_decode($image_parts[1]);
                $file_name=uniqid() . '.'.$image_type;
                $file = $destinationPath . $file_name;
                // saving in local storage
                file_put_contents($file, $image_base64);
            }

            $posts_obj = new Posts();
            $posts_obj->text=$data['text'];
            $posts_obj->user_id=$data['id'];
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