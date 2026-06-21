<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class TicketController extends Controller
{
    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'discord_id' => ['required', 'string', 'regex:/^\d{17,20}$/'],
            'channel_id' => ['required', 'string', 'regex:/^\d{17,20}$/'],
            'ticket_type' => ['nullable', 'string', 'in:support,purchase'],
        ]);

        $ticketType = $validated['ticket_type'] ?? 'support';
        $price = (int) config('services.midtrans.license_price', 50000);

        $qrUrl = null;
        $paymentStatus = 'unpaid';

        if ($ticketType === 'purchase') {
            $serverKey = config('services.midtrans.server_key');

            // Deteksi key belum dikonfigurasi (kosong atau masih placeholder)
            $keyConfigured = ! empty($serverKey) && ! str_contains($serverKey, 'XXXXXXXX');

            if (! $keyConfigured) {
                // Fitur pembayaran belum dikonfigurasi
                $qrUrl = null;
                $paymentStatus = 'unconfigured';
            } else {
                // Panggil Midtrans Core API untuk QRIS
                $isProd = config('services.midtrans.is_production', false);
                $baseUrl = $isProd ? 'https://api.midtrans.com/v2' : 'https://api.sandbox.midtrans.com/v2';
                $orderId = 'TKT-' . $validated['channel_id'] . '-' . time();

                try {
                    $response = Http::withBasicAuth($serverKey, '')
                        ->post("{$baseUrl}/charge", [
                            'payment_type' => 'qris',
                            'transaction_details' => [
                                'order_id'     => $orderId,
                                'gross_amount' => $price,
                            ],
                            'qris' => [
                                'acquirer' => 'gopay',
                            ],
                        ]);

                    if ($response->successful()) {
                        $actions = $response->json('actions', []);
                        foreach ($actions as $action) {
                            if (($action['name'] ?? '') === 'generate-qr-code') {
                                $qrUrl = $action['url'];
                                break;
                            }
                        }
                        // Fallback: cek field qr_string jika actions kosong
                        if (! $qrUrl) {
                            $qrStr = $response->json('qr_string');
                            if ($qrStr) {
                                $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($qrStr);
                            }
                        }
                        $paymentStatus = 'pending';
                        // Simpan order_id Midtrans untuk lookup saat cek pembayaran
                        $validated['midtrans_order_id'] = $orderId;
                    } else {
                        Log::error('Midtrans QRIS Generation Failed', ['body' => $response->json()]);
                        $qrUrl = null;
                        $paymentStatus = 'failed';
                    }
                } catch (\Exception $e) {
                    Log::error('Midtrans Exception', ['msg' => $e->getMessage()]);
                    $qrUrl = null;
                    $paymentStatus = 'failed';
                }
            }
        }

        $ticket = Ticket::create([
            'discord_id'         => $validated['discord_id'],
            'channel_id'         => $validated['channel_id'],
            'status'             => 'open',
            'ticket_type'        => $ticketType,
            'payment_status'     => $paymentStatus,
            'payment_amount'     => $ticketType === 'purchase' ? $price : null,
            'payment_qr_url'     => $qrUrl,
            'midtrans_order_id'  => $validated['midtrans_order_id'] ?? null,
        ]);

        $responseData = [
            'status'  => true,
            'message' => 'Tiket berhasil dibuat.',
            'data'    => $ticket,
        ];

        if ($paymentStatus === 'unconfigured') {
            $responseData['payment_notice'] = 'Fitur pembayaran sedang dalam tahap pengembangan. Hubungi admin untuk pembelian manual.';
        }

        return response()->json($responseData, 201);
    }

    public function process(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'channel_id' => ['required', 'string', 'regex:/^\d{17,20}$/'],
            'processed_by' => ['required', 'string', 'regex:/^\d{17,20}$/'],
        ]);

        $ticket = Ticket::where('channel_id', $validated['channel_id'])->first();

        if (! $ticket) {
            return response()->json([
                'status' => false,
                'message' => 'Tiket tidak ditemukan.',
            ], 404);
        }

        if ($ticket->isClosed()) {
            return response()->json([
                'status' => false,
                'message' => 'Tiket sudah ditutup.',
            ], 422);
        }

        $ticket->update([
            'status' => 'processing',
            'processed_by' => $validated['processed_by'],
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Tiket sedang diproses.',
            'data' => $ticket,
        ]);
    }

    public function close(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'channel_id' => ['required', 'string', 'regex:/^\d{17,20}$/'],
            'closed_by' => ['required', 'string', 'regex:/^\d{17,20}$/'],
        ]);

        $ticket = Ticket::where('channel_id', $validated['channel_id'])->first();

        if (! $ticket) {
            return response()->json([
                'status' => false,
                'message' => 'Tiket tidak ditemukan.',
            ], 404);
        }

        $ticket->update([
            'status' => 'closed',
            'closed_by' => $validated['closed_by'],
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Tiket berhasil ditutup.',
            'data' => $ticket,
        ]);
    }

    public function show(string $channelId): JsonResponse
    {
        $ticket = Ticket::where('channel_id', $channelId)->first();

        if (! $ticket) {
            return response()->json([
                'status' => false,
                'message' => 'Tiket tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Tiket ditemukan.',
            'data' => $ticket,
        ]);
    }

    public function checkPayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'channel_id' => ['required', 'string', 'regex:/^\d{17,20}$/'],
        ]);

        $ticket = Ticket::where('channel_id', $validated['channel_id'])->first();

        if (! $ticket) {
            return response()->json([
                'status' => false,
                'message' => 'Tiket tidak ditemukan.',
            ], 404);
        }

        if ($ticket->payment_status === 'paid') {
            return response()->json([
                'status' => true,
                'paid' => true,
                'key' => $ticket->license_key_generated,
                'message' => 'Pembayaran sudah lunas sebelumnya.'
            ]);
        }

        $serverKey = config('services.midtrans.server_key');
        $keyConfigured = ! empty($serverKey) && ! str_contains($serverKey, 'XXXXXXXX');

        if (! $keyConfigured) {
            return response()->json([
                'status'  => false,
                'message' => 'Fitur pembayaran sedang dalam tahap pengembangan. Hubungi admin.',
            ], 503);
        }

        $isProd = config('services.midtrans.is_production', false);
        $baseUrl = $isProd ? 'https://api.midtrans.com/v2' : 'https://api.sandbox.midtrans.com/v2';
        // Gunakan midtrans_order_id yang disimpan saat tiket dibuat
        $orderId = $ticket->midtrans_order_id ?? $ticket->channel_id;

        try {
            $response = Http::withBasicAuth($serverKey, '')
                ->get("{$baseUrl}/{$orderId}/status");

            if (!$response->successful()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal menghubungi Midtrans API untuk verifikasi.'
                ], 502);
            }

            $transactionStatus = $response->json('transaction_status');

            if (in_array($transactionStatus, ['settlement', 'capture'], true)) {
                $ticket->payment_status = 'paid';

                $discordBotService = app(\App\Services\DiscordBotService::class);
                $license = $discordBotService->generateLicenseForDiscordUser(
                    targetDiscordId: $ticket->discord_id,
                    actorDiscordId: $ticket->discord_id,
                    durationDays: 30,
                    licenseType: 'user'
                );

                $ticket->license_key_generated = $license->license_key;
                $ticket->save();

                return response()->json([
                    'status' => true,
                    'paid' => true,
                    'key' => $license->license_key,
                    'message' => 'Pembayaran berhasil terkonfirmasi! Lisensi Anda telah diterbitkan.'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Verify Midtrans Exception', ['msg' => $e->getMessage()]);
        }

        return response()->json([
            'status' => true,
            'paid' => false,
            'message' => 'Pembayaran belum terdeteksi. Silakan bayar terlebih dahulu lalu klik Cek Pembayaran.'
        ]);
    }

    public function midtransCallback(Request $request): JsonResponse
    {
        $orderId = $request->input('order_id');
        $transactionStatus = $request->input('transaction_status');

        if (empty($orderId)) {
            return response()->json(['status' => false, 'message' => 'Invalid payload.'], 400);
        }

        $ticket = Ticket::where('channel_id', $orderId)->first();

        if ($ticket && $ticket->payment_status !== 'paid') {
            if (in_array($transactionStatus, ['settlement', 'capture'], true)) {
                $ticket->payment_status = 'paid';

                $discordBotService = app(\App\Services\DiscordBotService::class);
                $license = $discordBotService->generateLicenseForDiscordUser(
                    targetDiscordId: $ticket->discord_id,
                    actorDiscordId: $ticket->discord_id,
                    durationDays: 30,
                    licenseType: 'user'
                );

                $ticket->license_key_generated = $license->license_key;
                $ticket->save();
            }
        }

        return response()->json(['status' => true, 'message' => 'Callback handled.']);
    }
}
