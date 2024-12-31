<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SectionRequest extends FormRequest
{
 public function rules()
    {
        switch ($this->method()) {
            case 'POST':
                return [
                    'session_id' => 'required|max:255',
                    'class_id' => 'required',
                    'name' => 'required|string|max:255',
                    'total_sit' => 'required|string|max:255',
                ];
                break;

            case 'PUT':
                return [
                    'session_id' => 'required|max:255',
                    'class_id' => 'required',
                    'name' => 'required|string|max:255',
                    'total_sit' => 'required|string|max:255',
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
            'session_id.required' => __('The session field is required.'),
            'class_id.required' => __('The class name field is required.'),
            'name.required' => __('The section name field is required.'),
            'total_sit.required' => __('The section sit field is required.'),
            'total_sit.number' => __('The section sit field is must be number.'),
        ];
    }
}