<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'max:50'],
            'progress' => ['nullable', 'integer', 'min:0', 'max:100'],
            'estimated_budget' => ['nullable', 'numeric', 'min:0'],
            'deadline' => ['nullable', 'date'],
        ];
    }
}
