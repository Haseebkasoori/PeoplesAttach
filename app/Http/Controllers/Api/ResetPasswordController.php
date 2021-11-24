<?php
   
namespace App\Http\Controllers\Api;
   
use App\Http\Controllers\Controller as BaseController;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Validator;
use App\Providers\RouteServiceProvider;
use App\Http\Requests\PasswordRestRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;
// use Mail;


class ResetPasswordController extends BaseController 
{
    /**
     * ResetPassword api
     *
     * @return \Illuminate\Http\Response
     */
    public function ResetPassword(PasswordRestRequest $request)
    {

            
            $data=$request->validated();
            $user = User::where('email',$data['email'])->first();
            
            // creating a new password
            $pool = '#$%^&*0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

            $password=substr(str_shuffle(str_repeat($pool, 5)), 0, 10);
            
            // data creation for email
            $details['password']=$password;
            $details['user_name']=$user->user_name;
            $details['email']=$data['email'];

            try{
                //send New Password mail
                $sendmail= \Mail::to($data['email'])->send(new \App\Mail\EmailNewPassword($details));

                //save data in database
                $user->password =  Hash::make($password);
                $update_data=$user->update();

                if (!$update_data) {
                    $data['error']="Have some Problem in Logout";
                    $data['message']="Someting went Worng";
                    return response()->error($data,404);
                }else{
                    // data creation for response
                    $data['data']=Null;
                    $data['message']=$user->user_name.' Please check your mail  '.$user->email.' for New Password';
                    return response()->success($data,200);
                }

            }catch(\Exception $ex){
                $data['error']=$ex->getMessage();
                $data['message']="Someting went Worng";
                return response()->error($data,404);
            }
        
    }       
}