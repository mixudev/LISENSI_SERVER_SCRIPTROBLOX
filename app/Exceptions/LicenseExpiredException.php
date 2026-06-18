<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class LicenseExpiredException extends Exception
{
    public function __construct(string $message = 'License has expired.')
    {
        parent::__construct($message);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $this->getMessage(),
        ], 410);
    }
}
