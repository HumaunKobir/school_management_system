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
                    'name' => 'required|string|max:255',
                    'total_sit' => 'required|string|max:255',
                ];
                break;

            case 'PUT':
                return [
                    'name' => 'required|string|max:255',
                    'total_sit' => 'required|number|max:255',
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
            'name.required' => __('The section name field is required.'),
            'total_sit.required' => __('The section sit field is required.'),
            'total_sit.number' => __('The section sit field is must be number.'),
        ];
    }
}