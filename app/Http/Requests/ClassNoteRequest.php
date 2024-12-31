<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClassNoteRequest extends FormRequest
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
                    'date' => 'required|max:255',
                    'class_note' => 'nullable|max:255',
                    'note_photo' => 'nullable|file|mimes:png,jpg,jpeg|max:25048',
                    'note_pdf' => 'nullable|file|max:25048',
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
                    'date' => 'required|max:255',
                    'class_note' => 'nullable|max:255',
                    'note_photo' => 'nullable|file|mimes:png,jpg,jpeg|max:25048',
                    'note_pdf' => 'nullable|file|max:25048',
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
            'class_id.required' => __('The class field is required.'),
            'section_id.required' => __('The section field is required.'),
            'group_id.required' => __('The group field is required.'),
            'teacher_id.required' => __('The teacher field is required.'),
            'subject_id.required' => __('The subject field is required.'),
            'date.required' => __('The date field is required.'),
            'photo.file' => __('The photo must be a file.'),
            'photo.mimes' => __('The photo must be a file of type: png, jpg, jpeg.'),
            'photo.max' => __('The photo may not be greater than :max kilobytes.'),
        ];
    }
}