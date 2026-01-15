<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class BaseApiController extends Controller
{
    protected function success($data = null, int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
        ], $code);
    }

    protected function error(
        string $message,
        int $code = 400,
        array $context = []
    ): JsonResponse {
        if ($code >= 500) {
            Log::error('[API ERROR]', $context);
        }

        return response()->json([
            'success' => false,
            'message' => $message,
        ], $code);
    }

    protected function exception(
        Throwable $e,
        string $message,
        array $context = []
    ): JsonResponse {
        Log::error($message, array_merge($context, [
            'exception' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]));

        return $this->error(
            'Erro interno ao processar requisição',
            500
        );
    }

    protected function baseContext(): array
    {
        return [
            'user_id' => auth()->id(),
            'timestamp' => now()->toIso8601String(),
        ];
    }
}