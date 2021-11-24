<?php

namespace App\Http\Requests;

use App\Services\DatabaseConnection;
use Illuminate\Foundation\Http\FormRequest;

class RegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_name' => 'required|string|unique_data:Users,user_name',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|string|unique_data:Users,user_name',
            'age' => 'required|numeric',
            'gender' => "required|string|in:Male,Female,Other",
            'date_of_birth' => 'required|date_format:Y-m-d|before:-13 years',
            'password'=> 'required|confirmed|string|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*#?&]/', //must be at least 8 characters in length, at least one lowercase and uppercase letter,at least one digit and a special character
            'profile_image' => 'array',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    // public function messages()
    // {
    //     return [
    //         'password.regex:/[a-z]/'=>'must be at least one lowercase latter',
    //         'password.regex:/[A-Z]/'=>'must be uppercase letter',
    //         'password.regex:/[0-9]/'=>'must be at least one digit',
    //         'password.regex:/[@$!%*#?&]/'=>'must be at least one special character'
    //     ];
    // }
}
