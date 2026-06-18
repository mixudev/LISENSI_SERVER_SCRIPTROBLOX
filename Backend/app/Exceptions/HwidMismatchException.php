<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class HwidMismatchException extends Exception
{
    public function __construct(string $message = 'HWID mismatch.')
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
