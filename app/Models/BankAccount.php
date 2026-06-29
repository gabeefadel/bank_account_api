<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'balance'];

    public $incrementing = false; 
    protected $keyType = 'string';


    public function credit(float $amount): void
    {
        $this->balance += $amount;
    }

 
    public function debit(float $amount): void
    {
        if ($this->hasInsufficientFundsFor($amount)) {
            throw new \InvalidArgumentException("Insufficient funds.");
        }

        $this->balance -= $amount;
    }

    public function hasInsufficientFundsFor(float $amount): bool
    {
        return $this->balance < $amount;
    }
}