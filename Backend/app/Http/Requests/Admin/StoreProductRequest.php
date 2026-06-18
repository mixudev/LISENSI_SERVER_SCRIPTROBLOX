<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use App\Services\GithubScriptService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreProductRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'version' => ['required', 'string', 'max:20'],
            'script_source' => ['required', 'in:local,github'],
            'script_folder' => ['nullable', 'string', 'max:100'],
            'github_repo' => ['nullable', 'string', 'max:300'],
            'github_branch' => ['nullable', 'string', 'max:100'],
            'github_path' => ['nullable', 'string', 'max:300'],
            'access_level' => ['required', 'in:user,admin'],
            'place_ids_raw' => ['nullable', 'string', 'max:2000'],
            'place_ids' => ['nullable', 'array'],
            'place_ids.*' => ['string', 'max:50', 'regex:/^\d+$/'],
            'status' => ['required', 'in:active,inactive,maintenance'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'place_ids' => Product::parsePlaceIdsFromRaw((string) $this->input('place_ids_raw', '')),
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $source = $this->input('script_source', 'local');

            if ($source === 'github') {
                if (! filled($this->input('github_repo'))) {
                    $validator->errors()->add('github_repo', 'Repo GitHub wajib diisi.');
                }

                if (! filled($this->input('github_path'))) {
                    $validator->errors()->add('github_path', 'Path loader.lua wajib diisi. Gunakan tombol Scan Repo untuk deteksi otomatis.');
                }

                try {
                    if (filled($this->input('github_repo'))) {
                        app(GithubScriptService::class)->normalizeRepo((string) $this->input('github_repo'));
                    }
                } catch (\RuntimeException $e) {
                    $validator->errors()->add('github_repo', $e->getMessage());
                }
            }

            if ($source === 'local' && ! filled($this->input('script_folder'))) {
                $validator->errors()->add('script_folder', 'Folder script lokal wajib dipilih.');
            }

            $excludeId = $this->route('product')?->id;
            $conflicts = Product::findPlaceIdConflicts($this->input('place_ids', []) ?? [], $excludeId);

            foreach ($conflicts as $conflict) {
                $validator->errors()->add(
                    'place_ids_raw',
                    "Place ID {$conflict['place_id']} sudah dipakai produk \"{$conflict['product_name']}\"."
                );
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    protected function passedValidation(): void
    {
        $source = $this->input('script_source', 'local');

        if ($source === 'github') {
            $github = app(GithubScriptService::class);
            $this->merge([
                'github_repo' => $github->normalizeRepo((string) $this->input('github_repo')),
                'github_branch' => $this->input('github_branch') ?: 'main',
            ]);
        } else {
            $this->merge([
                'github_repo' => null,
                'github_branch' => null,
                'github_path' => null,
            ]);
        }
    }
}
