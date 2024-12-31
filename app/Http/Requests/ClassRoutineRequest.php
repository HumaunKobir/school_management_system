<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClassRoutineRequest extends FormRequest
{
 public function rules()
    {
        switch ($this->method()) {
            case 'POST':
                return [
                    'session_id' => 'required|max:255',
                    'class_id' => 'required|max:255',
                    'section_id' => 'required|max:255',
                    'group_id' => 'required|max:255',
                    'teacher_id' => 'required|max:255',
                    'subject_id' => 'required|max:255',
                    'room_id' => 'required|max:255',
                    'day' => 'required|string|max:255',
                    'start_time' => 'required|max:255',
                    'end_time' => 'required|max:255',
                ];
                break;

            case 'PUT':
                return [
                    'session_id' => 'required|max:255',
                    'class_id' => 'required|max:255',
                    'section_id' => 'required|max:255',
                    'group_id' => 'required|max:255',
                    'teacher_id' => 'required|max:255',
                    'subject_id' => 'required|max:255',
                    'room_id' => 'required|max:255',
                    'day' => 'required|string|max:255',
                    'start_time' => 'required|max:255',
                    'end_time' => 'required|max:255',
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
            'class_id.required' => __('The first name field is required.'),
            'section_id.required' => __('The section field is required.'),
            'group_id.required' => __('The group field is required.'),
            'teacher_id.required' => __('The teacher field is required.'),
            'subject_id.required' => __('The subject field is required.'),
            'room_id.required' => __('The froom field is required.'),
            'day.required' => __('The day field is required.'),
            'start_time.required' => __('The start time field is required.'),
            'end_time.required' => __('The end time field is required.'),
        ];
    }
}