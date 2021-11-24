<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginReqeust;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Service\DatabaseConnection;
use App\Services\DatabaseConnection as ServicesDatabaseConnection;
use App\Services\JwtAuthentication;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class LoginController extends Controller
{



    /**
     * Get the Request .
     *
     * @return JwtToken
     */
    public function login(LoginReqeust $request){
        try{
            //connection
            $input_data=$request->validated();
            $connection = new ServicesDatabaseConnection;
            $database = $connection->getDatabase();
            $user= $database->Users->findOne([ 'email' => $request->email ]);
            if (!empty($user->email)) {
                if (Hash::check($input_data['password'], $user->password)) {
                    $user_data['email'] = $user->email;
                    $user_data['user_name'] = $user->user_name;
                    $user_data['_id'] = (string)$user->_id;

                    // create Jwt token
                    $Auth_key=JwtAuthentication::createJwtToken($user_data);
                        //insert data in Auth Token
                        $database->AuthToken->insertOne([
                            "token" => $Auth_key,
                            "user_id" => (string)$user->_id
                        ]);

                    $data['success']='User signin!!!';
                    $data['Authentication']=$Auth_key;
                    return response()->json($data, $this-> successStatus);

                }else{

                    $data['error']="Incorrect Password!!";
                    $data['message']="Someting went Worng";
                    return response()->error($data,404);
                }
            }else{
                $data['error']="Email not Register!!";
                $data['message']="Someting went Worng";
                return response()->error($data,404);
            }
        }catch(\Exception $ex){

            $data['error']=$ex->getMessage();
            $data['message']="Someting went Worng";
            return response()->error($data,500);

        }
    }
    //logout
    public function logout(Request $request){
        $token = $request->bearerToken();
        try {
            $connection = new DatabaseConnection;
            $collection = $connection->getConnection();
            $db = $connection->getdb();
            $decoded_data=JWT::decode($token, new Key("sumaila", 'HS256'));
            $collection->$db->AuthToken->deleteOne([
                '_id' => $decoded_data->data->id
            ]);
            return response()->json(['success'=>'Logout Successfully!!'], $this-> successStatus);
        }
        catch (\Exception $e) {
            return response()->json(['error'=>$e->getMessage()], 500);
        }
    }
}
