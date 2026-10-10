<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A migration original só cria `usuarios` quando a tabela ainda não
        // existe. Em bancos de instalações antigas, portanto, a coluna pode
        // estar faltando mesmo que a migration original já tenha sido executada.
        if (! Schema::hasTable('usuarios') || Schema::hasColumn('usuarios', 'pessoa_id')) {
            return;
        }

        Schema::table('usuarios', function (Blueprint $table) {
            $table->foreignId('pessoa_id')
                ->nullable()
                ->constrained('pessoas')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Esta correção é compatível com instalações em que a coluna já existia
        // antes da migration; removê-la no rollback poderia quebrar essas bases.
    }
};
