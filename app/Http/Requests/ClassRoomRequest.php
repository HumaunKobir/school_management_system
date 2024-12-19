<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClassRoomRequest extends FormRequest
{
 public function rules()
    {
        switch ($this->method()) {
            case 'POST':
                return [
                    'room_number' => 'required|max:255',
                    'capacity' => 'required|max:255',
                ];
                break;

            case 'PUT':
                return [
                    'room_number' => 'required|max:255',
                    'capacity' => 'required|max:255', 
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
            'room_number.required' => __('The room number field is required.'),
            'capacity.required' => __('The room capacity field is required.'),
        ];
    }
}