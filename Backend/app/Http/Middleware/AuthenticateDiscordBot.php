<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateDiscordBot
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredToken = config('services.discord_bot.token');

        if (! filled($configuredToken)) {
            return response()->json([
                'status' => false,
                'message' => 'Discord bot API is not configured on the server.',
            ], 503);
        }

        $providedToken = $request->bearerToken();

        if (! filled($providedToken) || ! hash_equals($configuredToken, $providedToken)) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized bot request.',
            ], 401);
        }

        return $next($request);
    }
}
