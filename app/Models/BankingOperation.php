<?php

namespace App\Models;

use App\Models\LogAccount;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankingOperation extends Model
{
    use HasFactory;

    protected $table = 'banking_operations';


    protected $fillable = [
        'id',
        'action',
    ];


    public function logs(): HasMany
    {
        return $this->hasMany(LogAccount::class, 'banking_operation_id', 'id');
    }
}