<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use \Illuminate\Contracts\Validation\Validator;

class UserSaveRequest extends FormRequest
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
            'user_name' => 'required|string|unique:users|max:100',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users|string|max:100',
            'gender' => 'required|string|in:Male,Female,Other',
            'date_of_birth' => 'required|date_format:Y-m-d|before:-13 years',
            'password'=> 'required|confirmed|string|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*#?&]/', //must be at least 8 characters in length, at least one lowercase and uppercase letter,at least one digit and a special character
            'phone_number' => 'required|string:11',
            'profile_image' => 'array',
        ];

    }

    public function failedValidation(Validator $validator)
    {
        $data['error']=$validator->errors();
        $data['message']="Someting went Worng";
        throw new HttpResponseException(response()->error($data, 404));
    }
}
