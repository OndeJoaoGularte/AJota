<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            // Relacionamento: a qual usuário esse gasto pertence?
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Descrição do gasto (ex: "Mercado", "Conta de Luz")
            $table->string('description');

            // O valor do gasto (10 dígitos no total, 2 casas decimais)
            $table->decimal('amount', 10, 2);

            // Tipo: entrada (income) ou saída (expense)
            $table->enum('type', ['income', 'expense']);

            // Data de quando o gasto ocorreu
            $table->date('date');

            $table->timestamps(); // Cria as colunas created_at e updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
