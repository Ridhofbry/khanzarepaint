<?php

namespace Database\Factories;

use App\Models\Voucher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Voucher>
 */
class VoucherFactory extends Factory
{
    protected $model = Voucher::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $discountType = fake()->randomElement(['fixed', 'percentage']);

        return [
            'code' => Voucher::generateCode(),
            'description' => fake()->sentence(),
            'discount_amount' => $discountType === 'fixed' ? fake()->numberBetween(50000, 500000) : null,
            'discount_percentage' => $discountType === 'percentage' ? fake()->numberBetween(5, 30) : null,
            'max_uses' => fake()->optional()->numberBetween(10, 1000),
            'current_uses' => 0,
            'usage_per_user' => 1,
            'is_active' => true,
            'expires_at' => fake()->dateTimeBetween('+1 week', '+1 year'),
            'starts_at' => fake()->dateTime(),
            'minimum_purchase_amount' => fake()->numberBetween(0, 500000),
            'applicable_to' => fake()->randomElement(['all', 'repaint_only', 'general_only']),
        ];
    }
}
