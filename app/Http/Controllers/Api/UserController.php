<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller as BaseController;
use App\Http\Requests\GetAllUserPostsRequest;
use App\Http\Requests\GetUserRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\UserDeleteRequest;
use Illuminate\Support\Facades\Validator;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Requests\UserSaveRequest;
use App\Http\Resources\UserResource;
use App\Jobs\EmailVarificationMailJob;
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
    public function verifyingEmail($email,$email_varified_token)
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
    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function Login(LoginRequest $request)
    {
        try{
            $user = User::where('email',$request->email)->first();
            $password_check=Hash::check($user['password'], $request->password);

            if (!$password_check) {
                // Creating JWT token
                $jwt_token=JwtAuthentication::createJwtToken($user);

                // save JWT token in DB
                $user->jwt_token= $jwt_token['token'];
                // try{
                    $update=$user->update();
                    if($update){
                        // data creation for response
                        $response_data['message']=strtoupper($user->user_name).' Welcome to the Application!!';
                        $response_data['data']['token_type']="Bearer";
                        $response_data['data']['Authenticaiton']=$jwt_token['token'];
                        $response_data['data']['Usre_data']=new UserResource($user);

                        return response()->success($response_data,200);
                    }
                // }catch(Exception $ex){
                //     info($ex->getMessage());
                // }
            }else{
                $response_data['error']=null;
                $response_data['message']="Someting went Worng";
                return response()->error($response_data, 500);
            }
        }catch(Exception $ex){
            info($ex->getMessage());
            $response_data['error']=null;
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
        $user->update($user);

        // data creation for response
        $data['data']=Null;
        $data['message']=strtoupper($user->user_name).', Please check your mail ('.$user->email.') for Email Varification';
        return response()->success($data,200);
    }


    /**
     * Logout api
     *
     * @return \Illuminate\Http\Response
     */
    public function Logout(Request $request)
    {
        $user=request()->user_data;
        // remove jwt token from database
        $user->jwt_token=Null;
        $update_data=$user->update();
        if (!$update_data) {
            $data['error']="Have some Problem in Logout";
            $data['message']="Someting went Worng";
            return response()->error($data,404);
        }else{
            // data creation for response
            $data['data']=Null;
            $data['message']='Logout Successfully';
            return response()->success($data,200);
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
            $data['message']='User Deleted Successfully!!';
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
                    $data['error']="User not exist on this ID";
                    $data['message']="Someting went Worng";
                    return response()->error($data, 404);
                }
            }
            catch (\Exception $e) {
                $data['error']=True;
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

}
