<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubjectRequest extends FormRequest
{
 public function rules()
    {
        switch ($this->method()) {
            case 'POST':
                return [
                    'class_id' => 'required|max:255',
                    'group_id' => 'required|max:255',
                    'name' => 'required|max:255',
                    'subject_code' => 'required|max:255',
                ];
                break;

            case 'PUT':
                return [
                    'class_id' => 'required|max:255',
                    'group_id' => 'required|max:255',
                    'name' => 'required|max:255',
                    'subject_code' => 'required|max:255',
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
            'class_id.required' => __('The Class field is required.'),
            'group_id.required' => __('The group field is required.'),
            'name.required' => __('The name field is required.'),
            'subject_code.required' => __('The subject code field is required.'),
        ];
    }
}