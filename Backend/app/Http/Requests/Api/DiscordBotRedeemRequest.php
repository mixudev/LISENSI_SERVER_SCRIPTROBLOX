<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class DiscordBotRedeemRequest extends FormRequest
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
            'license_key' => ['required', 'string', 'max:50'],
            'discord_id' => ['required', 'string', 'regex:/^\d{17,20}$/'],
            'display_name' => ['nullable', 'string', 'max:100'],
        ];
    }
}
