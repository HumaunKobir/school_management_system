<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentRequest extends FormRequest
{
 public function rules()
    {
        switch ($this->method()) {
            case 'POST':
                return [
                    'name' => 'required|string|max:255',
                    'father_name' => 'required|string|max:255',
                    'mother_name' => 'required|string|max:255',
                    'phone' => 'required|max:255',
                    'email' => 'required|max:255',
                    'address' => 'required|string|max:255',
                    'date_of_birth' => 'required|max:255',
                    'admission_date' => 'required|max:255',
                    'session_id' => 'required|max:255',
                    'class_id' => 'required|max:255',
                    'section_id' => 'required|max:255',
                    'group_id' => 'required|max:255',
                    'password' => 'required|max:255',
                    'photo' => 'file|mimes:png,jpg,jpeg|max:25048',
                ];
                break;

            case 'PUT':
                return [
                    'name' => 'required|string|max:255',
                    'father_name' => 'required|string|max:255',
                    'mother_name' => 'required|string|max:255',
                    'phone' => 'required|max:255',
                    'email' => 'nullable|max:255',
                    'address' => 'required|string|max:255',
                    'date_of_birth' => 'required|max:255',
                    'admission_date' => 'required|max:255',
                    'session_id' => 'required|max:255',
                    'class_id' => 'required|max:255',
                    'section_id' => 'required|max:255',
                    'group_id' => 'required|max:255',
                    'password' => 'required|max:255',
                    'photo' => 'nullable|file|mimes:png,jpg,jpeg|max:25048',
                ];
                break;
            case 'PATCH':

                break;
        }
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, mixed>
     */
    public function messages()
    {

        return [
            'name.required' => __('The name field is required.'),
            'father_name.required' => __('The father name field is required.'),
            'mother_name.required' => __('The first name field is required.'),
            'phone.required' => __('The mother name field is required.'),
            'address.required' => __('The address field is required.'),
            'date_of_birth.required' => __('The date of birth field is required.'),
            'admission_date.required' => __('The admission date field is required.'),
            'session_id.required' => __('The session field is required.'),
            'class_id.required' => __('The class name field is required.'),
            'section_id.required' => __('The section is required.'),
            'group_id.required' => __('The group field is required.'),
            'password.required' => __('The password field is required.'),
            'email.email' => __('Please enter a valid email address.'),
            'photo.file' => __('The photo must be a file.'),
            'photo.mimes' => __('The photo must be a file of type: png, jpg, jpeg.'),
            'photo.max' => __('The photo may not be greater than :max kilobytes.'),
        ];
    }
}