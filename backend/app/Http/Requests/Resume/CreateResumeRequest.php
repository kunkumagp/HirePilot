<?php

namespace App\Http\Requests\Resume;

use Illuminate\Foundation\Http\FormRequest;

class CreateResumeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'mimes:pdf,docx,txt', 'max:10240'],
        ];

    }

    public function messages(): array
    {
        return [
            'file.max' => 'File size must not exceed 10MB.',
            'file.mimes' => 'Supported formats: PDF, DOCX, TXT.',
        ];
    }
}
