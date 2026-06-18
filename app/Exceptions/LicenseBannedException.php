<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class LicenseBannedException extends Exception
{
    public function __construct(string $message = 'License is banned or suspended.')
    {
        parent::__construct($message);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $this->getMessage(),
        ], 403);
    }
}
