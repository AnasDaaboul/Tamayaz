<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PackageRequest extends FormRequest
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
            'name'=>['string' , 'required'],
            'price'=>['integer' ,'required'],
            'description'=>['string' , 'required'],
            'image'=>['required' , 'image' ],
            'courses' => ['array'],
            'courses.*.course_id' => ['required', 'exists:courses,id'],
            'sections' => ['array'],
            'sections.*.section_id' => ['required', 'exists:sections,id'],
        ];
    }
}