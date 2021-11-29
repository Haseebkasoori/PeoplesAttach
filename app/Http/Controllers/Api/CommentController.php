<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller as BaseController;
use App\Http\Requests\CommentCreateRequest;
use App\Http\Requests\CommentDeleteRequest;
use App\Http\Requests\CommentUpdateRequest;
use App\Http\Resources\PostResource;
use App\Models\Comments;
use Illuminate\Support\Facades\Validator;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\Posts;
use Exception;

// use Mail;


class CommentController extends BaseController
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function CreateComment(CommentCreateRequest $request)
    {
        try{
            if (empty($request->text)and empty($request->attachment)) {
                throw new Exception('Please write some text or upload any file for creating post');
                // dd($request->user_data->id);
            }else{
                $file_name=null;
                if (!empty($request->attachment)) {

                    // upload Attachment
                    $destinationPath = storage_path('api_data\comments\\');
                    $data_type_aux = explode("/", $request->attachment['mime']);
                    $attachment_type=$data_type_aux[0];
                    $attachment_extention=$data_type_aux[1];
                    $image_base64 = base64_decode($request->attachment['data']);
                    $file_name=$request->user_name.uniqid() . '.'.$attachment_extention;
                    $file = $destinationPath . $file_name;
                    // saving in local storage
                    file_put_contents($file, $image_base64);
                }
                $comment = new Comments();
                $comment->text=$request->text;
                $comment->User()->associate($request->user_data->id);
                $comment->Post()->associate($request->post_id);
                $comment->attachment=$file_name;
                // save post in db
                if($comment->save()){
                    $details['password']=$password;
                    $details['user_name']=$user->user_name;
                    $details['email']=$request->email;

                    //send New Comment mail
                    dispatch(new ForgotPasswordJob($details));

                    // return response
                    $data['data']=Null;
                    $data['message']='Post Created Successfully';
                    return response()->success($data,200);
                }else{
                    throw new Exception("have some problem, Try again");
                }
            }
        }catch(\Exception $ex){
            $data['error']=$ex->getMessage();
            $data['message']="Someting went Worng";
            return response()->error($data,404);
        }
    }
     //update Comment data
     public function UpdateComment(CommentUpdateRequest $request){
         try{
            $data_to_update=[];
            foreach ($request->all() as $key => $value) {
                if (in_array($key, ['text'])) {
                    $data_to_update[$key]=$value;
                }
            }
            if (!empty($request->attachment)) {

                // upload Attachment
                $destinationPath = storage_path('api_data\comments\\');
                $data_type_aux = explode("/", $request->attachment['mime']);
                $attachment_type=$data_type_aux[0];
                $attachment_extention=$data_type_aux[1];
                $image_base64 = base64_decode($request->attachment['data']);
                $file_name=$request->user_name.uniqid() . '.'.$attachment_extention;
                $file = $destinationPath . $file_name;
                // saving in local storage
                file_put_contents($file, $image_base64);
                $data_to_update['attacment']=$file_name;
            }

            //store your file into directory and db

            $comment=$request->comment_data;
            if($comment->update())
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
    public function DeleteComment(CommentDeleteRequest $request){
        try{
            $comment=$request->comment_data;
            if($comment->delete()){

                // return response
                $response_data['data']=null;
                $response_data['message']='comment Deleted SUccessfully';
                return response()->success($response_data,200);
            }else{
                throw new Exception('Comment not Exist');
            }
        }
        catch (\Exception $ex) {
            $response_data['error']=$ex->getMessage();
            $response_data['message']="Someting went Worng";
            return response()->error($response_data,404);
        }
    }
}
