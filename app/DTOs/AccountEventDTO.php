<?php

namespace App\DTOs;

use App\Enums\AccountEventType;

class AccountEventDTO
{
    public function __construct(
        public readonly AccountEventType $type,
        public readonly float $amount,
        public readonly ?string $origin = null,
        public readonly ?string $destination = null
    ) {}


    public static function fromRequest(array $data): self
    {
        return new self(
            type: AccountEventType::from($data['type']),
            amount: (float) $data['amount'],
            origin: $data['origin'] ?? null,
            destination: $data['destination'] ?? null
        );
    }
}