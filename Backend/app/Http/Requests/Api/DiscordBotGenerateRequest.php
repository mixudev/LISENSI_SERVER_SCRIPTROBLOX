<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class DiscordBotGenerateRequest extends FormRequest
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
            'target_discord_id' => ['required', 'string', 'regex:/^\d{17,20}$/'],
            'actor_discord_id' => ['required', 'string', 'regex:/^\d{17,20}$/'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'license_type' => ['nullable', 'in:user,admin'],
        ];
    }
}
