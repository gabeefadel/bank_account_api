<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    use HasFactory;

    protected $table = 'bank_accounts';

    protected $fillable = [
        'balance',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(LogAccount::class, 'bank_account_id', 'id');
    }
}