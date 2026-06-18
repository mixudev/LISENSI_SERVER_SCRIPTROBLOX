<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class InspectGithubRepositoryRequest extends FormRequest
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
            'github_repo' => ['required', 'string', 'max:300'],
            'github_branch' => ['nullable', 'string', 'max:100'],
        ];
    }
}
