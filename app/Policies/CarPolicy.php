<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Car;

class CarPolicy
{
    /**
     * Determine whether the user can update the car
     */
    public function update(User $user, Car $car): bool
    {
        return $user->id === $car->seller_id && $car->status !== 'sold';
    }

    /**
     * Determine whether the user can delete the car
     */
    public function delete(User $user, Car $car): bool
    {
        return $user->id === $car->seller_id;
    }

    /**
     * Determine whether the user can mark car as sold
     */
    public function markAsSold(User $user, Car $car): bool
    {
        return $user->id === $car->seller_id && $car->status === 'available';
    }
}
