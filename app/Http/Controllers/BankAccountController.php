<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountEventRequest;
use App\Http\Requests\BalanceRequest; 
use App\Http\Resources\BankAccountResource;
use App\Http\Resources\BalanceResource; 
use App\Services\AccountService;
use App\Services\AccountEventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\DTOs\AccountEventDTO;


use Exception;

class BankAccountController extends Controller
{

    protected AccountService $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    public function index() 
    {
        Artisan::call('migrate:fresh', ['--seed' => true]);
        return response()->json('OK', 200);
    }

    public function store(AccountEventRequest $request, AccountEventService $eventService): JsonResponse
    {
        try {
            $dto = AccountEventDTO::fromRequest($request->validated());
            $result = $eventService->execute($dto);

            return (new BankAccountResource($result))
                ->toResponse($request)
                ->setStatusCode(201);
        } catch (ModelNotFoundException|\InvalidArgumentException $e) {
            return response()->json(0, 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'An internal server error occurred.'], 500);
        }
    }

    public function balance(BalanceRequest $request): JsonResponse
    {
        try {
            $accountId = $request->query('account_id');
            $balance = $this->accountService->getBalance($accountId);
            
            return (new BalanceResource($balance))->toResponse($request);

        } catch (ModelNotFoundException $e) {
            return response()->json(0, 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'An internal server error occurred.'], 500);
        }
    }
}
