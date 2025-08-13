<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CoursePurchaseRequest extends FormRequest
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

            // 'cobon'=>['required' , 'min:12' , 'max:12' , 'exists:cobons,name'],
            'course_teacher_id'=>['required' , 'exists:course_teachers,id'],
            // 'package_id'=>['nullable'],

        ];
    }
}