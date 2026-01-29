<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove primeiro as tabelas com chaves estrangeiras (dependências)
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('model_has_permissions');
        
        // Remove as tabelas principais solicitadas
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions'); // Geralmente acompanha roles
        Schema::dropIfExists('activities');
        Schema::dropIfExists('reports');
        Schema::dropIfExists('noticias');
    }

    public function down(): void
    {
        // O rollback é deixado vazio pois o objetivo é a exclusão definitiva
    }
};
