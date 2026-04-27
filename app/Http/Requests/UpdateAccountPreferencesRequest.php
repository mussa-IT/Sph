<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAccountPreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'preferred_locale' => ['required', Rule::in(['en', 'sw', 'fr', 'ar'])],
            'timezone' => ['required', 'timezone'],
            'theme_preference' => ['required', Rule::in(['light', 'dark', 'system'])],
            'compact_mode' => ['nullable', 'boolean'],
            'comfortable_spacing' => ['nullable', 'boolean'],
            'sidebar_collapsed_default' => ['nullable', 'boolean'],
            'receive_product_updates' => ['nullable', 'boolean'],
            'receive_marketing_emails' => ['nullable', 'boolean'],
        ];
    }
}

