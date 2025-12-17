<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('cpf', 14)->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('user_type', ['common', 'merchant']);
            $table->decimal('balance', 15, 2)->default(0.00);
            $table->timestamps();

            $table->index('cpf');
            $table->index('email');
            $table->index('user_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

