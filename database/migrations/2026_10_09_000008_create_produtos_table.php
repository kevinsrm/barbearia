<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('produtos')) {
            return;
        }

        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')->nullable()->constrained('produto_categorias')->nullOnDelete();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->string('codigo', 40)->nullable()->unique();
            $table->string('codigo_barras', 60)->nullable()->index();
            $table->decimal('preco_custo', 10, 2)->default(0);
            $table->decimal('preco_venda', 10, 2)->default(0);
            $table->integer('estoque')->default(0);
            $table->integer('estoque_minimo')->default(0);
            $table->string('unidade', 20)->default('un');
            $table->boolean('ativo')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produtos');
    }
};
