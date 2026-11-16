<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_transaction_id')->constrained('wallet_transactions');
            $table->foreignId('account_id')->constrained('accounts');
            $table->enum('side', ['debit', 'credit']);
            $table->bigInteger('amount');
            $table->timestamps();
        });
        DB::statement("ALTER TABLE ledgers
            ADD CONSTRAINT chk_ledgers_amount
                CHECK (amount > 0)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ledgers');
    }
};
