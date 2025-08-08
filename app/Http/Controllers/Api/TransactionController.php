<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepositRequest;
use App\Http\Requests\TransferRequest;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class TransactionController extends Controller
{
    public function __construct(private readonly TransactionService $transactionService)
    {
    }

    public function deposit(DepositRequest $request, int $id): JsonResponse
    {
        try {
            $result = $this->transactionService->deposit($id, $request->validated('amount'));

            return response()->json([
                'message' => 'Funds deposited successfully',
                'data' => $result
            ]);
        } catch (Throwable $e) {
            Log::error('Error depositing funds', [
                'user_id' => $id,
                'amount' => $request->input('amount'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return response()->json([
            'message' => 'Error depositing funds',
            'error' => 'An unexpected error occurred.'
        ], 500);
    }

    public function transfer(TransferRequest $request, int $senderId): JsonResponse
    {
        try {
            $result = $this->transactionService->transfer(
                $senderId,
                $request->validated('recipient_id'),
                $request->validated('amount')
            );

            if (!isset($result['success']) || !$result['success']) {
                return response()->json([
                    'message' => $result['message'] ?? 'Transfer failed',
                    'data' => $result['data'] ?? []
                ], 400);
            }

            return response()->json([
                'message' => 'Funds transferred successfully',
                'data' => $result['data']
            ]);
        } catch (Throwable $e) {
            Log::error('Error transferring funds', [
                'sender_id' => $senderId,
                'recipient_id' => $request->input('recipient_id'),
                'amount' => $request->input('amount'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return response()->json([
            'message' => 'Error transferring funds',
            'error' => 'An unexpected error occurred.'
        ], 500);
    }
}
