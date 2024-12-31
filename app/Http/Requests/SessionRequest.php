<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SessionRequest extends FormRequest
{
 public function rules()
    {
        switch ($this->method()) {
            case 'POST':
                return [
                    'session_year' => 'required|string|max:255',
                ];
                break;

            case 'PUT':
                return [
                    'session_year' => 'required|string|max:255',
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
            'session_year.required' => __('The session field is required.'),
        ];
    }
}