<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('profile_photo_path')->nullable(); // Sua coluna
            $table->timestamp('email_verified_at')->nullable();
            $table->string('email_verification_code')->nullable(); // Sua coluna
            $table->string('password');
            $table->rememberToken();
            $table->timestamps(); // <-- Isso cria 'created_at' e 'updated_at'
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};