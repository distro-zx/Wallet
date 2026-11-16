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
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('name');
            $table->string('active_name')->nullable()->storedAs("IF(status = 'active', name, NULL)");
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->bigInteger('available_balance')->default(0);
            $table->bigInteger('current_balance')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'active_name']);

        });

        DB::statement("ALTER TABLE wallets
            ADD CONSTRAINT chk_wallets_available_balance
                CHECK (available_balance >= 0),
            ADD CONSTRAINT chk_wallets_current_balance
                CHECK (current_balance >= 0),
            ADD CONSTRAINT chk_wallets_cbl_gte_avl
                CHECK (current_balance >= available_balance)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
