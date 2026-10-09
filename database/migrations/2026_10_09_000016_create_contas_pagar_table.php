<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('contas_pagar')) {
            return;
        }

        Schema::create('contas_pagar', function (Blueprint $table) {
            $table->id();
            $table->string('descricao');
            $table->foreignId('fornecedor_id')->nullable()->constrained('fornecedores')->nullOnDelete();
            $table->foreignId('compra_id')->nullable()->constrained('compras')->nullOnDelete();
            $table->decimal('valor', 10, 2)->default(0);
            $table->date('vencimento')->index();
            $table->date('data_pagamento')->nullable();
            $table->string('forma_pagamento', 30)->nullable();
            $table->string('status', 20)->default('pendente')->index();
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contas_pagar');
    }
};
