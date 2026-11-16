<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WalletTransactionConstraintsTest extends TestCase
{
    use RefreshDatabase;

    private int $userId;
    private int $walletA;
    private int $walletB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userId = User::factory()->create()->id;

        $this->walletA = DB::table('wallets')->insertGetId([
            'user_id' => $this->userId,
            'name'    => 'Main',
        ]);

        $this->walletB = DB::table('wallets')->insertGetId([
            'user_id' => $this->userId,
            'name'    => 'Savings',
        ]);
    }

    private function insertTransaction(array $overrides): void
    {
        DB::table('wallet_transactions')->insert(array_merge([
            'user_id' => $this->userId,
            'ref_id'  => 'ref-001',
            'amount'  => 1000,
        ], $overrides));
    }

    public function test_database_accepts_deposit_into_a_wallet(): void
    {
        $this->insertTransaction([
            'txn_type'       => 'deposit',
            'from_wallet_id' => null,
            'to_wallet_id'   => $this->walletA,
        ]);

        $this->assertDatabaseHas('wallet_transactions', [
            'txn_type'       => 'deposit',
            'from_wallet_id' => null,
            'to_wallet_id'   => $this->walletA,
        ]);
    }

    public function test_database_rejects_withdraw_without_source_wallet(): void
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('chk_wallet_transactions_withdraw');

        $this->insertTransaction([
            'txn_type'       => 'withdraw',
            'from_wallet_id' => null,
            'to_wallet_id'   => null,
        ]);
    }

    public function test_database_accepts_withdraw_from_a_wallet(): void
    {
        $this->insertTransaction([
            'txn_type'       => 'withdraw',
            'from_wallet_id' => $this->walletA,
            'to_wallet_id'   => null,
        ]);

        $this->assertDatabaseHas('wallet_transactions', [
            'txn_type'       => 'withdraw',
            'from_wallet_id' => $this->walletA,
            'to_wallet_id'   => null,
        ]);
    }

    public function test_database_accepts_wallet_transfer(): void
    {
        $this->insertTransaction([
            'txn_type'       => 'transfer',
            'from_wallet_id' => $this->walletA,
            'to_wallet_id'   => $this->walletB,
        ]);

        $this->assertDatabaseHas('wallet_transactions', [
            'txn_type'       => 'transfer',
            'from_wallet_id' => $this->walletA,
            'to_wallet_id'   => $this->walletB,
        ]);
    }

    public function test_database_rejects_null_destination_wallet_transfer(): void
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('chk_wallet_transactions_transfer');

        $this->insertTransaction([
            'txn_type'       => 'transfer',
            'from_wallet_id' => $this->walletA,
            'to_wallet_id'   => null,
        ]);
    }

    public function test_database_rejects_same_wallet_transfer(): void
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('chk_wallet_transactions_transfer');

        $this->insertTransaction([
            'txn_type'       => 'transfer',
            'from_wallet_id' => $this->walletA,
            'to_wallet_id'   => $this->walletA,
        ]);
    }

    public function test_database_rejects_deposit_with_source_and_destination_wallet(): void
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('chk_wallet_transactions_deposit');

        $this->insertTransaction([
            'txn_type'       => 'deposit',
            'from_wallet_id' => $this->walletA,
            'to_wallet_id'   => $this->walletB,
        ]);
    }
}
