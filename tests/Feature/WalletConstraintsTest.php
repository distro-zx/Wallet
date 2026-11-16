<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WalletConstraintsTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_rejects_negative_available_balance(): void
    {
        // Arrange: a wallet needs an owner
        $user = User::factory()->create();

        // Assert: tell PHPUnit what failure we expect *before* it happens
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('chk_wallets_available_balance');

        // Act: go straight to the DB, bypassing all app code on purpose
        DB::table('wallets')->insert([
            'user_id'           => $user->id,
            'name'              => 'Main',
            'available_balance' => -1,
            'current_balance'   => 0,
        ]);
    }

    public function test_new_wallet_defaults_to_zero_balance_and_active(): void
    {
        $user = User::factory()->create();

        DB::table('wallets')->insert([
            'user_id'           => $user->id,
            'name'              => 'Main'
        ]);
        $this->assertDatabaseHas('wallets', [
            'name' => 'Main',
            'status' => 'active',
            'available_balance' => 0,
            'current_balance'   => 0,
        ]);

    }
}
