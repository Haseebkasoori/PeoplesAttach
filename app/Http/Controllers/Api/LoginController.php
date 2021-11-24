<?php
   
namespace App\Http\Controllers\Api;
   
use App\Http\Controllers\Controller as BaseController;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Validator;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;
// use Mail;


class LoginController extends BaseController 
{
    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function Login(Request $request)
    {
        $validator = Validator::make($request->json()->all(), [
            'email' => 'required|email|string|exists:users',
            'password' => 'required|min:8|string',
        ]);
        
        if($validator->fails()){
            $data['error']=$validator->errors();
            $data['message']="Someting went Worng";
            return response()->error($data,404);
        }else{
            $data=$validator->validated();
            $user = User::where('email',$data['email'])->first();
            // Creating JWT token
            $jwt_token=(new JwtController)->createJwtToken($user);

            // save JWT token in DB
            $user->jwt_token= $jwt_token['token'];
            $update=$user->update();
            
            if($update){
                // data creation for response
                $response_data['message']=strtoupper($user->user_name).' Welcome to the Application!!';
                $response_data['token_type']="Bearer";
                $response_data['Authenticaiton']=$jwt_token['token'];
                
                $data['data']=$response_data;
                $data['message']='sign in successfully!!';
                return response()->success($data,200);
            }
            
        }
    }
    /**
     * Logout api
     *
     * @return \Illuminate\Http\Response
     */
    public function Logout(Request $request)
    {
        $token=request()->bearerToken();
        
        // varify token if not expire return decoded data
        $decoded_data=(new JwtController)->varifyToken($token);
        $user=User::where("jwt_token",$token)->first();
        
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
       
}