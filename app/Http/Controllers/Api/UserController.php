<?php
   
namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller as BaseController;
use Illuminate\Support\Facades\Validator;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Http\Request;
use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
// use Mail;


class RegisterController extends BaseController 
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function UpdateUser(UserSaveRequest $request)
    {
        
        $data=$request->validated();
        
        $file_name=null;    
        // converting base64 decoded image to simple image if exist
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

        $user = new User();
        $user->user_name = $data['user_name'];
        $user->first_name = $data['first_name'];
        $user->last_name = $data['last_name'];
        $user->phone_number = $data['phone_number'];
        $user->email = $data['email'];
        $user->attachment = $file_name;
        $user->password =  Hash::make($data['password']);
        
        $email_varified_token = md5($data['user_name']);
        $user->email_varified_token= $email_varified_token;
        $user->save();

        // data creation for email
        $details['link']=url('api/emailConfirmation/'.$data['email'].'/'.$email_varified_token);
        $details['user_name']=$data['user_name'];
        $details['email']=$data['email'];

        //send verification mail
        $sendmail= \Mail::to($data['email'])->send(new \App\Mail\EmailVarification($details));

        // data creation for response
        $data['data']=Null;
        $data['message']=strtoupper($user->user_name).', Please check your mail ('.$user->email.') for Email Varification';
        return response()->success($data,200);
    }
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function VerifyEmail($email,$email_varified_token)
    {
        $newarray['email']=$email;
        $newarray['email_varified_token']=$email_varified_token;
        $validator = Validator::make($newarray, $messages=[
            'email' => "exists:users",
            'email_varified_token' => "exists:users",
        ],[
            'email_varified_token.exists'=>"Please use Correct Link, or Create Another Link",
            'email.exists'=>"Please use correct Link",
        ]);
        
        if($validator->fails()){
            $data['error']=$validator->errors();
            $data['message']="Someting went Worng";
            return response()->error($data,404);
        }else{
            $data=$validator->validated();
            $user = User::where("email",$email)->where('email_varified_token',$email_varified_token)->first();
            $user->email_varified_token= "";
            $user->email_verified_at= date('Y-m-d h:i:s');
            $user->save();

            // data creation for response
            $data['data']=Null;
            $data['message']=$user->user_name.' Your Account Has Been Verified';
            return response()->success($data,200);
        }
    }

}