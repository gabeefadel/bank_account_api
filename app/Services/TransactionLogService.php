<?php

namespace App\Services;

use App\DTOs\AccountEventDTO;
use App\Enums\AccountEventType;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TransactionLogService
{

    public function logTransaction(AccountEventDTO $dto): void
    {
        $timestamp = Carbon::now()->format('Y-m-d H:i:s');
        $formattedAmount = number_format($dto->amount, 2, '.', '');

        $message = match ($dto->type) {
            AccountEventType::DEPOSIT => sprintf(
                "Deposit of %s processed for account %s on date %s",
                $formattedAmount, $dto->destination, $timestamp
            ),
            AccountEventType::WITHDRAW => sprintf(
                "Withdrawal of %s processed from account %s on date %s",
                $formattedAmount, $dto->origin, $timestamp
            ),
            AccountEventType::TRANSFER => sprintf(
                "Bank transfer from account %s in the amount of %s to account %s on date %s",
                $dto->origin, $formattedAmount, $dto->destination, $timestamp
            ),
        };

        Log::info(json_encode([
            'status' => 'success',
            'event' => $dto->type->value,
            'message' => $message,
            'timestamp' => $timestamp,
            'details' => [
                'amount' => $dto->amount,
                'origin' => $dto->origin,
                'destination' => $dto->destination,
            ]
        ]));
    }

    public function logFailure(AccountEventDTO $dto, string $reason): void
    {
        $timestamp = Carbon::now()->format('Y-m-d H:i:s');
        $formattedAmount = number_format($dto->amount, 2, '.', '');

        $message = match ($dto->type) {
            AccountEventType::DEPOSIT => sprintf(
                "Failed deposit of %s for account %s on date %s. Reason: %s",
                $formattedAmount, $dto->destination ?? 'unknown', $timestamp, $reason
            ),
            AccountEventType::WITHDRAW => sprintf(
                "Failed withdrawal of %s from account %s on date %s. Reason: %s",
                $formattedAmount, $dto->origin ?? 'unknown', $timestamp, $reason
            ),
            AccountEventType::TRANSFER => sprintf(
                "Failed bank transfer from account %s in the amount of %s to account %s on date %s. Reason: %s",
                $dto->origin ?? 'unknown', $formattedAmount, $dto->destination ?? 'unknown', $timestamp, $reason
            ),
        };

        Log::warning(json_encode([
            'status' => 'failed',
            'event' => $dto->type->value,
            'message' => $message,
            'timestamp' => $timestamp,
            'reason' => $reason,
            'details' => [
                'amount' => $dto->amount,
                'origin' => $dto->origin,
                'destination' => $dto->destination,
            ]
        ]));
    }
}