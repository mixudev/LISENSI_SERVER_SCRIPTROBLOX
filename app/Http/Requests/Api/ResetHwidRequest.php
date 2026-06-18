<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ResetHwidRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Hanya pemilik lisensi yang boleh reset via API
        return $this->user() !== null;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'key' => ['required', 'string', 'regex:/^LZD-[A-F0-9]{6}-[A-F0-9]{6}-[A-F0-9]{6}-[A-F0-9]{6}$/'],
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
        ];
    }
}
