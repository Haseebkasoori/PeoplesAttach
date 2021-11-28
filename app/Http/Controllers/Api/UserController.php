<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller as BaseController;
use App\Http\Requests\AddFriendRequest;
use App\Http\Requests\GetAllUserPostsRequest;
use App\Http\Requests\GetUserRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\PasswordRestRequest;
use App\Http\Requests\UserDeleteRequest;
use Illuminate\Support\Facades\Validator;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Requests\UserSaveRequest;
use App\Http\Requests\VarifyEmailReqeust;
use App\Http\Resources\UserResource;
use App\Jobs\EmailVarificationMailJob;
use App\Jobs\ForgotPasswordJob;
use App\Jobs\FriendRequestEmailJob;
use App\Models\FriendRequest;
use App\Models\User;
use App\Services\JwtAuthentication;
use Exception;
use Illuminate\Support\Facades\Request;


class UserController extends BaseController
{
        /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(UserSaveRequest $request)
    {
        try{
            $data=$request->validated();

            $file_name=null;
            // converting base64 decoded image to simple image if exist
            if (!empty($data['profile_image'])) {

                // upload Attachment
                $destinationPath = storage_path('api_data\users\\');
                $data_type_aux = explode("/", $data['profile_image']['mime']);
                $attachment_type=$data_type_aux[0];
                $attachment_extention=$data_type_aux[1];
                $image_base64 = base64_decode($data['profile_image']['data']);
                $file_name=$data['user_name'].uniqid() . '.'.$attachment_extention;
                $file = $destinationPath . $file_name;
                // saving in local storage
                file_put_contents($file, $image_base64);
            }

            $user = new User();
            foreach ($data as $key => $value) {
                $user->$key = $value;
            }
            $user->profile_image = $file_name;
            $user->password =  bcrypt($data['password']);
            $email_varified_token = md5($data['user_name']);
            $user->email_varified_token= $email_varified_token;
            $user->save();

            // data creation for email
            $details['link']=url('api/emailConfirmation/'.$data['email'].'/'.$email_varified_token);
            $details['user_name']=$data['user_name'];
            $details['email']=$data['email'];

            //send verification mail
            try{
                dispatch(new EmailVarificationMailJob($details));

            }catch(Exception $ex){
                info($ex->getMessage());
            }

            // data creation for response
            $response_data['data']=null;
            $response_data['message']=strtoupper($user->user_name).', Please check your mail ('.$user->email.') for Email Varification';
            return response()->success($response_data,200);
        }catch(Exception $ex){
            info($ex->getMessage());
            $response_data['error']=null;
            $response_data['message']="Someting went Worng";
            return response()->error($response_data, 500);
        }
    }
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function verifyingEmail(VarifyEmailReqeust $reqeust,$email,$email_varified_token)
    {
        try{

            $user = User::where("email",$email)->where('email_varified_token',$email_varified_token)->first();
            $user->email_varified_token= "";
            $user->email_verified_at= date('Y-m-d h:i:s');
            $user->save();

            // data creation for response
            $data['data']=Null;
            $data['message']=$user->user_name.' Your Account Has Been Verified';
            return response()->success($data,200);
        }catch(Exception $ex ){
            info($ex->getMessage());
            $response_data['error']=null;
            $response_data['message']="Someting went Worng";
            return response()->error($response_data, 500);
        }
    }
/**
     * ResetPassword api
     *
     * @return \Illuminate\Http\Response
     */
    public function ResetPassword(PasswordRestRequest $request)
    {
        $user = User::where('email',$request->email)->first();

        // creating a new password
        $pool = '#$%^&*0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $password=substr(str_shuffle(str_repeat($pool, 5)), 0, 10);

        // data creation for email
        $details['password']=$password;
        $details['user_name']=$user->user_name;
        $details['email']=$request->email;

        try{
            //send New Password mail
            dispatch(new ForgotPasswordJob($details));

            //save data in database
            $user->password =  Hash::make($password);
            $update_data=$user->update();

            if (!$update_data) {
                throw new Exception('Have some problem in update password please try again later');
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

    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function Login(LoginRequest $request)
    {
        try{
            $user = User::where('email',$request->email)->first();
            if(!empty($user)){
                $password_check=Hash::check($user['password'], $request->password);

                if (!$password_check) {
                    // Creating JWT token
                    $jwt_token=JwtAuthentication::createJwtToken($user);

                    // save JWT token in DB
                    $user->jwt_token= $jwt_token['token'];
                    try{
                        $update=$user->update();
                        if($update){
                            // data creation for response
                            $response_data['message']=strtoupper($user->user_name).' Welcome to the Application!!';
                            $response_data['data']['token_type']="Bearer";
                            $response_data['data']['Authenticaiton']=$jwt_token['token'];
                            $response_data['data']['Usre_data']=new UserResource($user);

                            return response()->success($response_data,200);
                        }
                    }catch(Exception $ex){
                        info($ex->getMessage());
                    }
                }else{
                    throw new Exception("Invalid Credentionl !!");

                }
            }else{

                throw new Exception("Invalid Credentionl !!");

            }
        }catch(Exception $ex){
            info($ex->getMessage());
            $response_data['error']=$ex->getMessage();
            $response_data['message']="Someting went Worng";
            return response()->error($response_data, 500);
        }
    }

    /**
     * Update User request
     *
     * @return \Illuminate\Http\Response
     */
    public function UpdateUser(UserUpdateRequest $request)
    {
        try{
            $file_name=null;
            $data_to_update = [];
            foreach ($request->all() as $key => $value) {
                if (in_array($key, ['user_name', 'first_name', 'last_name', 'phone_number', 'phone_number','gender','date_of_birth'])) {
                    $data_to_update[$key]=$value;
                }
            }
            // converting base64 decoded image to simple image if exist
            if (!empty($request->profile_image)) {
                // upload Attachment
                $destinationPath = storage_path('api_data\users\\');
                $data_type_aux = explode("/", $request->profile_image['mime']);
                $attachment_type=$data_type_aux[0];
                $attachment_extention=$data_type_aux[1];
                $image_base64 = base64_decode($request->profile_image['data']);
                $file_name=$request->user_name.uniqid() . '.'.$attachment_extention;
                $file = $destinationPath . $file_name;
                // saving in local storage
                file_put_contents($file, $image_base64);
                $data_to_update['profile_image']=$file_name;
            }

            $user = new User();
            if(!empty($request->password)){
                $user->password =  Hash::make($request->password);
            }

            $user = User::make($user);
            if($user->update($user)){

                // data creation for response
                $data['data']=Null;
                $data['message']=strtoupper($user->user_name).', Please check your mail ('.$user->email.') for Email Varification';
                return response()->success($data,200);

            }else{

                throw new Exception("Have Problem in Updation");

            }

        }catch(Exception $ex ){
            info($ex->getMessage());
            $response_data['error']=null;
            $response_data['message']="Someting went Worng";
            return response()->error($response_data, 500);
        }
    }


    /**
     * Logout api
     *
     * @return \Illuminate\Http\Response
     */
    public function Logout(Request $request)
    {
        try{
            $user=request()->user_data;
            // remove jwt token from database
            $user->jwt_token=Null;
            $update_data=$user->update();
            if ($update_data) {

                // data creation for response
                $data['data']=Null;
                $data['message']='Logout Successfully';
                return response()->success($data,200);

            }else{
                // data creation for response
                throw new Exception("Have some Problem in Logout");

            }
        }catch(Exception $ex){
            $data['error']=$ex->getMessage();
            $data['message']="Someting went Worng";
            return response()->error($data,404);
        }
    }

    /**
     * Delete User api
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */

    public function DeleteUser(UserDeleteRequest $request)
    {
        try{
            User::where('id',$request->user_id)->delete();

            // data creation for response
            $data['data']= null;
            $data['message']='User Deleted Successfully!!';
            return response()->success($data,200);
        }
        catch (\Exception $e) {
            $data['error']="Have some Problem in Deleting Account";
            $data['message']="Someting went Worng";
            return response()->error($data,404);
        }

    }

    /**
     * Search User api
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */

     public function SearchUser(Request $request)
    {
        try{

            $user= User::where('user_name','like','%'.$request->user_name.'%')->first();

            $user= User::make($user->toArray());
            // data creation for response
            $data['data']= new UserResource($user);
            $data['message']='User data!!';
            return response()->success($data,200);
        }
        catch (\Exception $e) {
            $data['error']=True;
            $data['message']="Someting went Worng";
            return response()->error($data, 404);
        }
    }

        //Fetch Single user data
    public function GetUserById(GetUserRequest $request)
        {
            try{
                $user_data=User::find($request->user_id);
                if(!empty($user_data)){
                    $user= User::make($user_data->toArray());
                    // data creation for response
                    $data['data']= new UserResource($user);
                    $data['message']='User Deleted Successfully!!';
                    return response()->success($data,200);
                }else{

                    throw new Exception("User not exist on this ID");

                }
            }
            catch (\Exception $ex) {

                $data['error']=$ex->getMessage();
                $data['message']="Someting went Worng";
                return response()->error($data, 404);

            }
        }

        //Fetch all users data
    public function GetAllUsers(Request $request)
    {
        try{
            $user_data=UserResource::collection(User::all());
            if(!empty($user_data)){

                 // data creation for response
                $data['data']= $user_data;
                $data['message']='Users Data!!';
                return response()->success($data,200);
            }else{
                $data['error']="Users not exist";
                $data['message']="Someting went Worng";
                return response()->error($data, 404);
            }
        }
        catch (\Exception $e) {
            return response()->json(['error'=>$e->getMessage()], 500);
        }
    }

    // Create friend request
    public function SendFriendRequest(AddFriendRequest $request){
        try {
            $friendRequest = new FriendRequest();
            $friendRequest->sender_id = request()->user_data->id;
            $friendRequest->reciever_id = $request->reciever_id;
            $friendRequest->save();
            $receiver_user = User::find($request->reciever_id);
            // dd($receiver_user);
            $email_data['receiver_user_name'] = $receiver_user->name;
            $email_data['sender_user_name'] = request()->user_data->name;
            $email_data['link']=url('friendrequest/GetAllFriendRequest/');
            // send email
            dispatch(new FriendRequestEmailJob($email_data, $receiver_user->email));

            $data['data']= new UserResource(request()->user_data);
            $data['message']='Friend Reqeust Sended!!';
            return response()->success($data,200);
        }
        catch (\Exception $e) {
            info($e->getMessage());
            $data['error']=null;
            $data['message']="Someting went Worng";
            return response()->error($data, 500);
        }
    }


}
