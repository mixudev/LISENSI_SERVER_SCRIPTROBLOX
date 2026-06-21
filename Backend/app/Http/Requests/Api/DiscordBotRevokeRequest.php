<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class DiscordBotRevokeRequest extends FormRequest
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
        ];
    }
}
