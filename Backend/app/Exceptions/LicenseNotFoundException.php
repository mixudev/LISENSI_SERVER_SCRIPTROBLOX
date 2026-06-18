<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class LicenseNotFoundException extends Exception
{
    public function __construct(string $message = 'License key not found.')
    {
        parent::__construct($message);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $this->getMessage(),
        ], 404);
    }
}
