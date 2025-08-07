<?php

namespace App\Services;

use App\Models\TransactionLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TransactionService
{
    private const TYPE_DEPOSIT = 'deposit';
    private const TYPE_TRANSFER = 'transfer';
    private const STATUS_SUCCESS = 'success';
    private const STATUS_FAILED = 'failed';

    /**
     * Deposit funds to a user's account.
     *
     * @param int $userId
     * @param float $amount
     * @return array
     * @throws ModelNotFoundException
     */
    public function deposit(int $userId, float $amount): array
    {
        return DB::transaction(function () use ($userId, $amount) {
            $user = User::lockForUpdate()->findOrFail($userId);

            $balanceBefore = $user->balance;
            $user->balance += $amount;
            $user->save();

            $this->logDeposit($userId, $amount, $balanceBefore, $user->balance);

            return [
                'user_id' => $user->id,
                'new_balance' => $user->balance,
                'amount_deposited' => $amount,
            ];
        });
    }

    /**
     * Transfer funds from one user to another.
     *
     * @param int $senderId
     * @param int $recipientId
     * @param float $amount
     * @return array
     * @throws ModelNotFoundException
     */
    public function transfer(int $senderId, int $recipientId, float $amount): array
    {
        return DB::transaction(function () use ($senderId, $recipientId, $amount) {
            $sender = User::lockForUpdate()->findOrFail($senderId);
            $senderBalanceBefore = $sender->balance;

            if ($sender->balance < $amount) {
                $this->logTransfer(
                    $senderId,
                    $recipientId,
                    $amount,
                    $senderBalanceBefore,
                    $senderBalanceBefore,
                    self::STATUS_FAILED,
                    'Transfer failed: Insufficient balance'
                );

                return [
                    'success' => false,
                    'message' => 'Insufficient balance',
                    'data' => [
                        'user_id' => $sender->id,
                        'current_balance' => $sender->balance,
                        'required_amount' => $amount,
                    ],
                ];
            }

            $recipient = User::lockForUpdate()->findOrFail($recipientId);

            $sender->balance -= $amount;
            $recipient->balance += $amount;

            $sender->save();
            $recipient->save();

            $this->logTransfer(
                $senderId,
                $recipientId,
                $amount,
                $senderBalanceBefore,
                $sender->balance,
                self::STATUS_SUCCESS,
                "Transfer from user {$senderId} to user {$recipientId}"
            );

            return [
                'success' => true,
                'data' => [
                    'sender_id' => $sender->id,
                    'sender_new_balance' => $sender->balance,
                    'recipient_id' => $recipient->id,
                    'recipient_new_balance' => $recipient->balance,
                    'amount_transferred' => $amount,
                ],
            ];
        });
    }

    /**
     * Log a deposit transaction.
     */
    private function logDeposit(int $userId, float $amount, float $before, float $after): void
    {
        TransactionLog::create([
            'transaction_type' => self::TYPE_DEPOSIT,
            'user_id' => $userId,
            'amount' => $amount,
            'status' => self::STATUS_SUCCESS,
            'balance_before' => $before,
            'balance_after' => $after,
            'notes' => 'Deposit transaction',
        ]);
    }

    /**
     * Log a transfer transaction.
     */
    private function logTransfer(
        int    $senderId,
        int    $recipientId,
        float  $amount,
        float  $before,
        float  $after,
        string $status,
        string $notes
    ): void
    {
        TransactionLog::create([
            'transaction_type' => self::TYPE_TRANSFER,
            'user_id' => $senderId,
            'recipient_id' => $recipientId,
            'amount' => $amount,
            'status' => $status,
            'balance_before' => $before,
            'balance_after' => $after,
            'notes' => $notes,
        ]);
    }
}
