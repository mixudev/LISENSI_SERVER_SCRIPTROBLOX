<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreLicenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'license_type' => ['required', 'in:user,admin'],
            'user_id'      => ['nullable', 'exists:users,id'],
            'duration_days'=> ['nullable', 'integer', 'min:0'],
            'notes'        => ['nullable', 'string', 'max:1000'],
        ];
    }
}
