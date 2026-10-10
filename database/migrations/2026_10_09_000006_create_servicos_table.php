<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('servicos')) {
            return;
        }

        Schema::create('servicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servico_categoria_id')->nullable()->constrained('servico_categorias')->nullOnDelete();
            $table->string('nome');
            $table->string('descricao')->nullable();
            $table->unsignedSmallInteger('duracao')->default(30);
            $table->decimal('valor', 10, 2)->default(0);
            $table->decimal('percentual_comissao', 5, 2)->default(0);
            $table->boolean('ativo')->default(true)->index();
            $table->boolean('agendamento_online')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servicos');
    }
};
