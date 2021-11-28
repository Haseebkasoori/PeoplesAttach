<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PostCreateRequest extends FormRequest
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
            'text' => 'string|max:255',
            'visibility' => 'required|in:Private,Public',
            'attachment' => 'array'
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $data['error']=$validator->errors();
        $data['message']="Someting went Worng";
        throw new HttpResponseException(response()->error($data, 404));
    }
}
