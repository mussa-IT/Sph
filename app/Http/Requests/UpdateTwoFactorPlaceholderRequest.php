<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTwoFactorPlaceholderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'two_factor_enabled' => ['required', 'boolean'],
            'two_factor_channel' => ['nullable', Rule::in(['authenticator', 'email', 'sms'])],
        ];
    }
}

