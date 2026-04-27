<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnalyzeIdeaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'idea' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'idea.required' => 'Please describe your project idea so the assistant can generate a plan.',
            'idea.min' => 'Please provide a more detailed idea with at least 10 characters.',
            'idea.max' => 'Your project idea is too long. Please keep it under 1000 characters.',
        ];
    }
}
