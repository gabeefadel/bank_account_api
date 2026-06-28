<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('log_accounts', function (Blueprint $table) {
            $table->id();
            
            // Chaves estrangeiras apontando para as tabelas corretas
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->onDelete('cascade');
            $table->foreignId('banking_operation_id')->constrained('banking_operations');
            
            $table->string('message');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_accounts');
    }
};
