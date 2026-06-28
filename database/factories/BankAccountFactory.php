<?php

namespace Database\Factories;

use App\Models\BankAccount;
use Illuminate\Database\Eloquent\Factories\Factory;


class BankAccountFactory extends Factory
{

    protected $model = BankAccount::class;


    public function definition(): array
    {
        return [
            'balance' => $this->faker->randomFloat(2, 0, 5000), 
        ];
    }
}