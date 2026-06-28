<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountEventRequest;
use App\Http\Requests\BalanceRequest; 
use App\Http\Resources\BankAccountResource;
use App\Http\Resources\BalanceResource; 
use App\Services\AccountService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\ValidationException;

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

    public function store(AccountEventRequest $request): JsonResponse
    {
        try {

        } catch (Exception) 
        {
            return response()->json(['error' => 'Transaction failed securely.'], 400);
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
