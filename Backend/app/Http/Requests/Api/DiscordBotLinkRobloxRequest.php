<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class DiscordBotLinkRobloxRequest extends FormRequest
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
            'discord_id' => ['required', 'string', 'regex:/^\d{17,20}$/'],
            'roblox_username' => ['nullable', 'string', 'max:64'],
        ];
    }
}
