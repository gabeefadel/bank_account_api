<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

public function up(): void
{
    Schema::create('banking_operations', function (Blueprint $table) {
        $table->id(); 
        $table->string('action'); 
        $table->timestamps();
    });
}


    public function down(): void
    {
        Schema::dropIfExists('banking_operations');
    }
};
