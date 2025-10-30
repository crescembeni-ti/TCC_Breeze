<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            
            // 1. Chave Estrangeira (PK) para ligar ao cadastro do usuário
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); 
            
            // 2. Dados Redundantes do Solicitante (puxados do Auth::user())
            $table->string('nome_solicitante'); 
            $table->string('email_solicitante'); 

            // 3. Campos que o usuário preenche (adaptados de sua migração) 
            $table->string('bairro');
            $table->string('rua');
            $table->string('numero', 10); 
            $table->text('descricao'); 
            
            // NOTA: Os campos 'full_name' e 'email' originais foram substituídos pelos campos nome_solicitante e email_solicitante
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};