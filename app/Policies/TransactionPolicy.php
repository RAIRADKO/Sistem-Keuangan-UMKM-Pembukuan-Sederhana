<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransactionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the transaction.
     */
    public function view(User $user, Transaction $transaction): bool
    {
        return $this->belongsToUserStore($user, $transaction);
    }

    /**
     * Determine whether the user can update the transaction.
     */
    public function update(User $user, Transaction $transaction): bool
    {
        return $this->belongsToUserStore($user, $transaction);
    }

    /**
     * Determine whether the user can delete the transaction.
     */
    public function delete(User $user, Transaction $transaction): bool
    {
        return $this->belongsToUserStore($user, $transaction);
    }

    /**
     * Check if the transaction belongs to the user's current store.
     */
    private function belongsToUserStore(User $user, Transaction $transaction): bool
    {
        $store = $user->currentStore();
        
        if (!$store) {
            return false;
        }
        
        return $transaction->store_id === $store->id;
    }
}
