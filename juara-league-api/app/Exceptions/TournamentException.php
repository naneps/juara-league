<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class TournamentException extends Exception
{
    protected $errorCode;
    protected $statusCode;

    public function __construct(string $message, string $errorCode, int $statusCode = 400)
    {
        parent::__construct($message);
        $this->errorCode = $errorCode;
        $this->statusCode = $statusCode;
    }

    public function render($request): JsonResponse
    {
        return response()->json([
            'error_code' => $this->errorCode,
            'message' => $this->getMessage(),
        ], $this->statusCode);
    }
}
