<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // GitHub Personal Access Token — untuk fetch script dari private repo
    // REKOMENDASI: Classic PAT (ghp_) dengan scope "repo" untuk private repository.
    // Alternatif: Fine-grained PAT (github_pat_) dengan permission Contents: Read per repo.
    // Buat di: https://github.com/settings/tokens
    'github' => [
        'pat' => env('GITHUB_PAT'),
        // Di Windows/local, SSL CA sering belum terpasang — default nonaktif saat APP_ENV=local
        'verify_ssl' => env('GITHUB_VERIFY_SSL', env('APP_ENV') === 'production'),
    ],

    // Discord Bot Server — machine-to-machine auth (Bearer token)
    'discord_bot' => [
        'token' => env('DISCORD_BOT_API_TOKEN'),
        'admin_discord_ids' => array_values(array_filter(array_map(
            trim(...),
            explode(',', (string) env('DISCORD_BOT_ADMIN_IDS', ''))
        ))),
    ],

    'roblox' => [
        'client_id' => env('ROBLOX_CLIENT_ID'),
        'client_secret' => env('ROBLOX_CLIENT_SECRET'),
        'redirect_uri' => env('ROBLOX_REDIRECT_URI'),
    ],

    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'license_price' => env('MIDTRANS_LICENSE_PRICE', 50000), // default 50rb
    ],

];
