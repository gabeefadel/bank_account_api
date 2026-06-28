<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogAccount extends Model
{
    use HasFactory;

    protected $table = 'log_accounts';

    protected $fillable = [
        'bank_account_id',
        'banking_operation_id',
        'message',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id', 'id');
    }

    public function operation(): BelongsTo
    {
        return $this->belongsTo(BankingOperation::class, 'banking_operation_id', 'id');
    }
}