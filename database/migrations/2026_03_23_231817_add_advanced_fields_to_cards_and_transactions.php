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
        // 1. Adicionamos as novas regras no Cartão de Crédito
        Schema::table('credit_cards', function (Blueprint $table) {
            $table->decimal('limit', 10, 2)->default(0)->after('due_day'); // Limite total do cartão
            $table->decimal('max_spend', 10, 2)->nullable()->after('limit'); // Alerta de meta de gastos (opcional)
            $table->string('color')->default('purple')->after('max_spend'); // A cor/ícone do cartão
        });

        // 2. Adicionamos o status de "Pago" nas Transações
        Schema::table('transactions', function (Blueprint $table) {
            // Por padrão, se for Pix/Dinheiro, já entra como Pago (true). 
            // Mas se for no cartão, o sistema vai salvar como false (Pendente).
            $table->boolean('is_paid')->default(true)->after('date');
        });
    }

    public function down(): void
    {
        Schema::table('credit_cards', function (Blueprint $table) {
            $table->dropColumn(['limit', 'max_spend', 'color']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('is_paid');
        });
    }
};
