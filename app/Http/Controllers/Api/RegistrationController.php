<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Mail\EmailVarification;
use App\Http\Requests\RegistrationRequest;
use App\Services\DatabaseConnection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;


class RegistrationController extends Controller
{
    public $successStatus = 200;
    /**
 * Register api
 *
 * @return \Illuminate\Http\Response
 */
    public function register(RegistrationRequest $request)
    {
        try{
            // creating database and collection variables

            $connection = new DatabaseConnection;
            $database = $connection->getDatabase();

            $input=$request->validated();
            $input['password'] = bcrypt($input['password']);
            $email_verification_token=md5($input['user_name']);  //for encoding
            $input['email_verification_token']=$email_verification_token;
            $file_name=null;
            // converting base64 decoded image to simple image if exist
            if (!empty($input['profile_image'])) {
                // upload Attachment
                $destinationPath = storage_path('\app\public\users\\');
                $input_type_aux = explode("/", $input['profile_image']['mime']);
                $attachment_type=$input_type_aux[0];
                $attachment_extention=$input_type_aux[1];
                $image_base64 = base64_decode($input['profile_image']['data']);
                $file_name=$input['user_name'].uniqid() . '.'.$attachment_extention;
                $file = $destinationPath . $file_name;
                // saving in local storage
                file_put_contents($file, $image_base64);
            }
            $input['profile_image'] = $file_name;
            $input['friend_request']=[];
            $input['posts']=[];
            $input['created_at'] = date('Y-m-d h:i:s');

            $insertOneResult = $database->Users->insertOne($input);

            $email_details['email_verification_token']=$email_verification_token;
            $email_details['email']=$input['email'];
            $email_details['user_name'] = $input['user_name'];
            //for generate link in URL

            $email_details['link']=url('user/emailConfirmation/'.$input['email'].'/'.$email_verification_token);

            // $sendmail= Mail::to($input['email'])->send(new EmailVarification($email_details));
            $success['success']=$insertOneResult->isAcknowledged();
            $success['insert_id']=$insertOneResult->GetInsertedId()->__toString();
            $success['user_name'] =  $input['user_name'] . " register successfully! for verifying please chk your email";

            // data creation for response
            $data['data']=Null;
            $data['message']=strtoupper($input['user_name']).',$email_verification_token Please check your mail ('.$input['email'].') for Email Varification,And that link valid till'.now()->addMinutes(30);
            return response()->success($data,200);
        }
        catch (\Exception $e) {
            $data['error']=$e->getMessage();
            $data['message']="Someting went Worng";
            return response()->error($data,500);
        }
    }
//     //verifying email
    public function verifyingEmail($email,$token){
        // try{
            // creating database and collection variables

            $connection = new DatabaseConnection;
            $database = $connection->getDatabase();
            $user =  $database->Users->findOne([ 'email' => $email ]);

            if (!empty($user['user_name'])) {
                if ( $token == $user['email_verification_token']) {
                    $user->email_verification_token = null;
                    $user->email_verified_at = date('Y-m-d h:i:s');
                    $database->Users->updateOne([
                    'email' => $user->email ],
                    [ '$set' => $user
                ]);
                // data creation for response
                $data['data']=Null;
                $data['message']='Email Verified!!!';
                return response()->success($data,200);
                }
                else{

                    $data['error']='Verification link already used!';
                    $data['message']="Someting went Worng";
                    return response()->error($data,404);
                }
            }else{

                $data['error']='Email not Register';
                $data['message']="Someting went Worng";
                return response()->error($data,404);
            }
    // }
    //     catch (\Exception $e) {
    //         return response()->json(['error'=>$e->getMessage()], 500);
    //     }
}
}
