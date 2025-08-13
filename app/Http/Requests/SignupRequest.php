<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SignupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name'=>['required' , 'min:3' , 'max:24'],
            'mobile_number' => [
           'required',
            // 'regex:/^([34567][0-9]{7})$/'
            'regex:/^(\\+974|974|0974)[34567][0-9]{7}$/',


    'unique:users,mobile_number',
],

            'password'=>['required','min:8' , 'max:64'],
            'Gender'=>['required'],
            'email'=>['required' , 'email' , 'unique:users,email'],
            'school_name'=>['required' , 'max:64'],
            'description' => ['required'],



        ];
    }


}