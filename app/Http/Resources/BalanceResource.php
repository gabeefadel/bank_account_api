<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\JsonResponse;

class BalanceResource extends JsonResource
{

    public function toResponse($request): JsonResponse
    {
        return response()->json((float) $this->resource, 200);
    }
}