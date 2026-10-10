<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('comissoes')) {
            return;
        }

        Schema::create('comissoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funcionario_id')->constrained('funcionarios')->cascadeOnDelete();
            $table->foreignId('venda_id')->nullable()->constrained('vendas')->nullOnDelete();
            $table->foreignId('agendamento_id')->nullable()->constrained('agendamentos')->nullOnDelete();
            $table->decimal('valor_base', 10, 2)->default(0);
            $table->decimal('percentual', 5, 2)->default(0);
            $table->decimal('valor', 10, 2)->default(0);
            $table->date('data')->index();
            $table->date('data_pagamento')->nullable();
            $table->string('status', 20)->default('pendente')->index();
            $table->string('observacoes')->nullable();
            $table->timestamps();

            $table->index(['funcionario_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comissoes');
    }
};
