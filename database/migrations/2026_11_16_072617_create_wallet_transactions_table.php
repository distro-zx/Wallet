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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_wallet_id')->nullable()->constrained('wallets');
            $table->foreignId('to_wallet_id')->nullable()->constrained('wallets');
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('ref_id');
            $table->enum('txn_type', ['deposit', 'withdraw', 'transfer']);
            $table->enum('status', ['pending', 'processing', 'failed', 'completed'])->default('pending');
            $table->bigInteger('amount');
            $table->timestamps();

            $table->unique(['user_id', 'ref_id']);

        });

        DB::statement("ALTER TABLE wallet_transactions
            ADD CONSTRAINT chk_wallet_transactions_amount
                CHECK (amount > 0),
            ADD CONSTRAINT chk_wallet_transactions_deposit
                CHECK (txn_type <> 'deposit'
                    OR (from_wallet_id IS NULL AND to_wallet_id IS NOT NULL)),
            ADD CONSTRAINT chk_wallet_transactions_withdraw
                CHECK (txn_type <> 'withdraw'
                    OR (from_wallet_id IS NOT NULL AND to_wallet_id IS NULL)),
            ADD CONSTRAINT chk_wallet_transactions_transfer
                CHECK (txn_type <> 'transfer'
                    OR (from_wallet_id IS NOT NULL
                        AND to_wallet_id IS NOT NULL
                        AND from_wallet_id <> to_wallet_id))
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
