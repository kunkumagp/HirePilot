<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreChannelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:telegram,email,inapp'],
            'enabled' => ['sometimes', 'boolean'],
            'target' => ['sometimes', 'nullable', 'string', 'max:255'],
            'credentials' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
