<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ActivateLicenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // API publik, tidak perlu auth
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'key' => ['required', 'string', 'regex:/^LZD-[A-F0-9]{6}-[A-F0-9]{6}-[A-F0-9]{6}-[A-F0-9]{6}$/'],
            'hwid' => ['required', 'string', 'min:4', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'key.required' => 'License key wajib diisi.',
            'key.regex' => 'Format license key tidak valid.',
            'hwid.required' => 'HWID wajib diisi.',
        ];
    }
}
