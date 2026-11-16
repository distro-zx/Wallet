<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Wallet extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function outgoingTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class, 'from_wallet_id');
    }

    public function incomingTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class, 'to_wallet_id');
    }

    public function transactions(): Builder
    {
        return WalletTransaction::query()->where(function (Builder $query) {
            $query->where('from_wallet_id', $this->id)
                ->orWhere('to_wallet_id', $this->id);
        });
    }

    public function account(): hasOne
    {
        return $this->hasOne(Account::class);
    }
}
