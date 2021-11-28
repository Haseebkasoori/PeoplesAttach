<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller as BaseController;
use App\Http\Requests\GetUserPostRequest;
use App\Http\Requests\PostDeleteRequest;
use App\Http\Requests\PostUpdateRequest;
use App\Http\Resources\PostResource;
use Illuminate\Support\Facades\Validator;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\Posts;
use Exception;
use phpDocumentor\Reflection\PseudoTypes\True_;

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
        try{
        // converting base64 decoded image to simple image
        $file_name=null;
            if (!empty($request->attachment)) {

                // upload Attachment
                $destinationPath = storage_path('api_data\posts\\');
                $data_type_aux = explode("/", $request->attachment['mime']);
                $attachment_type=$data_type_aux[0];
                $attachment_extention=$data_type_aux[1];
                $image_base64 = base64_decode($request->attachment['data']);
                $file_name=$request->user_name.uniqid() . '.'.$attachment_extention;
                $file = $destinationPath . $file_name;
                // saving in local storage
                file_put_contents($file, $image_base64);
            }
            // dd($request->user_data->id);
            $post = new Posts();
            if (empty($request->text)and empty($request->attachment)) {
                throw new Exception('Please write some text or upload any file for creating post');
            }else{
                $post->text=$request->text;
                $post->user_id=$request->user_data->id;
                $post->visibility=$request->visibility;
                $post->attachment=$file_name;
                if ($post->save()) {
                    // return response
                    $response_data['data']=new PostResource($post) ;
                    $response_data['message']='Post Created Successfully';
                    return response()->success($response_data,200);

                }else{
                    throw new Exception('There is some problem in saving post please try again latter.');
                }
            }
        }catch(\Exception $ex){
            info($ex->getMessage());
            $response_data['error']=$ex->getMessage();
            $response_data['message']="Someting went Worng";
            return response()->error($response_data,404);
        }
    }
    //Get Single Post
    public function GetPost(GetUserPostRequest $request)
    {
        try{
            //check status
            if(!empty($request->single_post_data)){

                // return response
                $response_data['data']=new PostResource($request->single_post_data);
                $response_data['message']='Post Data';
                return response()->success($response_data,200);


            }
            else{
                throw new Exception("Try Again Letter");
            }
        }
        catch (\Exception $ex) {
            $response_data['error']=$ex->getMessage();
            $response_data['message']="Someting went Worng";
            return response()->error($response_data,404);
        }
    }
     //update Post data
     public function UpdatePost(PostUpdateRequest $request){
         try{
            $data_to_update=[];
            foreach ($request->all() as $key => $value) {
                if (in_array($key, ['text', 'visibility'])) {
                    $data_to_update[$key]=$value;
                }
            }
            if (!empty($request->attachment)) {
                // upload Attachment
                $destinationPath = storage_path('\app\public\post\\');
                $input_type_aux = explode("/", $request->attachment['mime']);
                $attachment_extention=$input_type_aux[1];
                $image_base64 = base64_decode($request->attachment['data']);
                $file_name=uniqid() . '.'.$attachment_extention;
                $file = $destinationPath . $file_name;
                // saving in local storage
                file_put_contents($file, $image_base64);
                $data_to_update['attacment']=$file_name;
            }
            //store your file into directory and db

            $post=$request->single_post_data;
            if($post->update())
            {
                $response_data['data']=new PostResource($request->single_post_data);
                $response_data['message']='Post Data';
                return response()->success($response_data,200);
            }else{

                throw new Exception("There is Problem Try Again Letter");

            }
        }
        catch (\Exception $ex) {
            $response_data['error']=$ex->getMessage();
            $response_data['message']="Someting went Worng";
            return response()->error($response_data,404);
        }
    }
    //Delete post
    public function DeletePost(PostDeleteRequest $request){
        try{
            $post=$request->single_post_data;
            if($post){
                $post->delete();
                // return response
                $response_data['data']=null;
                $response_data['message']='Post Deleted SUccessfully';
                return response()->success($response_data,200);
            }else{
                throw new Exception('Post not Exist');
            }
        }
        catch (\Exception $ex) {
            $response_data['error']=$ex->getMessage();
            $response_data['message']="Someting went Worng";
            return response()->error($response_data,404);
        }
    }

}
