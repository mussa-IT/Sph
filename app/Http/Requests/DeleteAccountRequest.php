<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'delete_password' => ['required', 'current_password'],
            'confirm_delete' => ['required', 'accepted'],
        ];
    }
}
