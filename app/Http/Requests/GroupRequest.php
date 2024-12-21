<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GroupRequest extends FormRequest
{
 public function rules()
    {
        switch ($this->method()) {
            case 'POST':
                return [
                    'class_id' => 'required|max:255',
                    'name' => 'required|string|max:255',
                ];
                break;

            case 'PUT':
                return [
                    'class_id' => 'required',
                    'name' => 'required|string|max:255',
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
            'class_id.required' => __('The class name field is required.'),
            'name.required' => __('The group name field is required.'),
        ];
    }
}