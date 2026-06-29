<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\BankAccount;

class BankAccountResource extends JsonResource
{

    public function toArray(Request $request): array
    {
 
        if ($this->resource instanceof BankAccount) {
            return [
                'id' => (string) $this->id,
                'balance' => (float) $this->balance,
            ];
        }

        if (is_array($this->resource)) {
            $payload = [];

            if (isset($this->resource['origin'])) {
                $payload['origin'] = [
                    'id' => (string) $this->resource['origin']->id,
                    'balance' => (float) $this->resource['origin']->balance,
                ];
            }

            if (isset($this->resource['destination'])) {
                $payload['destination'] = [
                    'id' => (string) $this->resource['destination']->id,
                    'balance' => (float) $this->resource['destination']->balance,
                ];
            }

            return $payload;
        }

        return parent::toArray($request);
    }

    public static function withoutWrapping()
    {
        parent::withoutWrapping();
    }
}