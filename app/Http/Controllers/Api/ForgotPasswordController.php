<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller; 
use Illuminate\Support\Facades\Mail;
use App\Mail\ForgotPassword;
use App\Http\Requests\ForgotRequest;
use App\Service\DatabaseConnection;

class ForgotPasswordController extends Controller
{
    public $successStatus = 200;
    public function forgotPassword(ForgotRequest $request)
{
    try{
        $connection = new DatabaseConnection;       
        $collection = $connection->getConnection();  
        $db = $connection->getdb();
        $input=$request->validated();
        $user_data=  $collection->$db->Users->findOne([ 'email' => $request->email ]);
        $string="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz!@#$%^&*()1234567890";
        $password=substr(str_shuffle(str_repeat($string, 12)), 0, 12);
        $user_data->password=bcrypt($password);
        //for generate link in URL
        $collection->$db->Users->updateOne([
            'email' => $user_data->email ],
            [ '$set' => $user_data
        ]);
        Mail::to($input['email'])->send(new ForgotPassword($password));
        return response()->json(['success'=>"New Password Send to Your Mail!"], $this-> successStatus); 
}
    catch (\Exception $e) {            
        return response()->json(['error'=>$e->getMessage()], 500);                    
    }
}

}
