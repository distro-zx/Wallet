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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['wallet', 'bank', 'fee_revenue']);
            $table->foreignId('wallet_id')->nullable()->unique()->constrained()->restrictOnDelete();
            $table->timestamps();
        });

        DB::statement("ALTER TABLE accounts ADD CONSTRAINT chk_accounts_wallet
            CHECK ((type = 'wallet') = (wallet_id IS NOT NULL))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
