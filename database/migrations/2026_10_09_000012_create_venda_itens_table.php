<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('venda_itens')) {
            return;
        }

        Schema::create('venda_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venda_id')->constrained('vendas')->cascadeOnDelete();
            $table->foreignId('produto_id')->nullable()->constrained('produtos')->nullOnDelete();
            $table->foreignId('servico_id')->nullable()->constrained('servicos')->nullOnDelete();
            $table->string('descricao');
            $table->unsignedSmallInteger('quantidade')->default(1);
            $table->decimal('valor_unitario', 10, 2)->default(0);
            $table->decimal('valor_total', 10, 2)->default(0);
            $table->timestamps();

            $table->index(['produto_id']);
            $table->index(['servico_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venda_itens');
    }
};
