<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('tarefas');
    }

    public function down(): void
    {
        Schema::create('tarefas', function (Blueprint $table) {
            $table->id();
            // recrie aqui as colunas que existiam
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->timestamps();
        });
    }
};
