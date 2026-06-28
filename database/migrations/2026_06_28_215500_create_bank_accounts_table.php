<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
 
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id(); // Conta ID
            // 10 dígitos no total, 2 decimais (ex: 99.999.999,99)
            $table->decimal('balance', 10, 2)->default(0.00); 
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
