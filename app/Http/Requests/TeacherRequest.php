<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TeacherRequest extends FormRequest
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
                    'email' => 'required|string|max:255',
                    'address' => 'required|string|max:255',
                    'date_of_birth' => 'required|max:255',
                    'education_level' => 'required|string|max:255',
                    'jonning_date' => 'required|max:255',
                    'password' => 'required|max:255',
                    'email' => 'email|unique:teachers,email|max:255',
                    'file' => 'nullable|file',
                    'photo' => 'file|mimes:png,jpg,jpeg|max:25048',
                ];
                break;

            case 'PUT':
                return [
                    'name' => 'required|string|max:255',
                    'father_name' => 'required|string|max:255',
                    'mother_name' => 'required|string|max:255',
                    'phone' => 'required|max:255',
                    'email' => 'required|string|max:255',
                    'address' => 'required|string|max:255',
                    'date_of_birth' => 'required|max:255',
                    'education_level' => 'required|string|max:255',
                    'jonning_date' => 'required|max:255',
                    'password' => 'required|max:255',
                    'email' => 'email|max:255|unique:teachers,id,' . $this->id,
                    'file' => 'nullable|file',
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
            'name.required' => __('The first name field is required.'),
            'email.required' => __('The email field is required.'),
            'email.email' => __('Please enter a valid email address.'),
            'email.unique' => __('This email address is already taken.'),
            'photo.file' => __('The photo must be a file.'),
            'photo.mimes' => __('The photo must be a file of type: png, jpg, jpeg.'),
            'photo.max' => __('The photo may not be greater than :max kilobytes.'),
        ];
    }
}