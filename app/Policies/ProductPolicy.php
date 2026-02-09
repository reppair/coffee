<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Product $product): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Product $product): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Product $product): Response|bool
    {
        if (! $user->isAdmin()) {
            return false;
        }

        if ($this->hasActiveInventory($product)) {
            return Response::deny('Cannot delete product with active inventory. Adjust all stock to zero first.');
        }

        return true;
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, Product $product): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Product $product): Response|bool
    {
        if (! $user->isAdmin()) {
            return false;
        }

        if ($this->hasActiveInventory($product)) {
            return Response::deny('Cannot permanently delete product with active inventory. Adjust all stock to zero first.');
        }

        return true;
    }

    private function hasActiveInventory(Product $product): bool
    {
        return $product->bulkStocks()->where('quantity_grams', '>', 0)->exists()
            || $product->packageStocks()->where('quantity', '>', 0)->exists();
    }
}
