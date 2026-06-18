<?php

namespace App\Jobs;

use App\Models\License;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

/**
 * Job untuk mengirim notifikasi email ke user yang lisensinya akan expired.
 * Dijalankan oleh scheduler setiap hari.
 */
class SendExpiryNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [1, 5, 10];

    public function __construct(public readonly License $license) {}

    public function handle(): void
    {
        $user = $this->license->user;

        if ($user === null || ! $user->is_active) {
            return;
        }

        // Email notifikasi dikirim di sini.
        // Mailable akan dibuat di Fase 3.
        // Mail::to($user->email)->send(new LicenseExpiryMail($this->license));
    }

    public function failed(\Throwable $exception): void
    {
        report($exception);
    }
}
