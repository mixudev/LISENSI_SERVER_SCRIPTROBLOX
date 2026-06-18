<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class DiscordBotDiscordIdRequest extends FormRequest
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
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->query('discord_id')) {
            $this->merge([
                'discord_id' => $this->query('discord_id'),
            ]);
        }
    }
}
