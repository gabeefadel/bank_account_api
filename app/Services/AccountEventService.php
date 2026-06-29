<?php

namespace App\Services;

use App\DTOs\AccountEventDTO;
use App\Enums\AccountEventType;
use App\Repositories\BankAccountRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AccountEventService
{
    public function __construct(
        protected BankAccountRepository $repository,
        protected TransactionLogService $logService
    ) {}

    public function execute(AccountEventDTO $dto): array
    {
        try {
            return DB::transaction(function () use ($dto) {
                $result = match ($dto->type) {
                    AccountEventType::DEPOSIT => $this->handleDeposit($dto),
                    AccountEventType::WITHDRAW => $this->handleWithdraw($dto),
                    AccountEventType::TRANSFER => $this->handleTransfer($dto),
                };

                $this->logService->logTransaction($dto);

                return $result;
            });
        } catch (\Exception $e) {
            $this->logService->logFailure($dto, $e->getMessage());
            throw $e;
        }
    }

    protected function handleDeposit(AccountEventDTO $dto): array
    {
 
        $account = $this->repository->findForUpdate($dto->destination) 
            ?? $this->repository->create($dto->destination, 0);

        $account->credit($dto->amount);
        $this->repository->updateBalance($account, $account->balance);

        return ['destination' => $account];
    }

    protected function handleWithdraw(AccountEventDTO $dto): array
    {
        $account = $this->repository->findForUpdate($dto->origin);

        if (!$account) {
            throw new ModelNotFoundException("Origin account not found.");
        }

        $account->debit($dto->amount);
        $this->repository->updateBalance($account, $account->balance);

        return ['origin' => $account];
    }

    protected function handleTransfer(AccountEventDTO $dto): array
    {
        $originAccount = $this->repository->findForUpdate($dto->origin);

        if (!$originAccount) {
            throw new ModelNotFoundException("Origin account not found.");
        }

        $destinationAccount = $this->repository->findForUpdate($dto->destination)
            ?? $this->repository->create($dto->destination, 0);

        $originAccount->debit($dto->amount);
        $destinationAccount->credit($dto->amount);

        $this->repository->updateBalance($originAccount, $originAccount->balance);
        $this->repository->updateBalance($destinationAccount, $destinationAccount->balance);

        return [
            'origin' => $originAccount,
            'destination' => $destinationAccount
        ];
    }
}