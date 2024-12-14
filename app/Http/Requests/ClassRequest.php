<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClassRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function rules()
    {
        switch ($this->method()) {
            case 'POST':
                return [
                    'class_name' => 'required|string|max:255',
                    'section' => 'required|string|max:255',
                ];
                break;

            case 'PUT':
                return [
                    'class_name' => 'required|string|max:255',
                    'section' => 'required|string|max:255',
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
            'class_name.required' => __('The first name field is required.'),
            'section.required' => __('The last name field is required.'),
        ];
    }
}
