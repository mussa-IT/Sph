<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'priority' => ['required', 'string', 'in:low,medium,high'],
            'due_date' => ['nullable', 'date'],
        ];
    }
}
