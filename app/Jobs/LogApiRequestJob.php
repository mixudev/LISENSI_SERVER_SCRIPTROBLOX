<?php

namespace App\Jobs;

use App\Models\ApiLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Job untuk mencatat API request secara async via Queue.
 * Dilakukan async agar tidak menambah latensi response API.
 */
class LogApiRequestJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 5;

    /**
     * @param  array{
     *   license_id?: int|null,
     *   endpoint: string,
     *   method: string,
     *   ip: string,
     *   user_agent?: string|null,
     *   license_key_used?: string|null,
     *   hwid_used?: string|null,
     *   roblox_username?: string|null,
     *   roblox_place_id?: string|null,
     *   inject_step?: string|null,
     *   status: string,
     *   http_code: int,
     *   response_message?: string|null,
     *   error_detail?: string|null,
     *   response_time_ms?: int|null
     * }  $data
     */
    public function __construct(public readonly array $data)
    {
        // Gunakan queue 'default' agar diproses oleh worker standar.
        // Jika tidak ada worker berjalan, job akan tetap tersimpan di DB dan
        // diproses saat worker dijalankan.
        $this->onQueue('default');
    }

    public function handle(): void
    {
        ApiLog::create($this->data);
    }

    public function failed(\Throwable $exception): void
    {
        // Gagal logging tidak boleh crash — cukup report ke log file
        report($exception);
    }
}
