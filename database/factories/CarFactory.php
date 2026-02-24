<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Car;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Car>
 */
class CarFactory extends Factory
{
    protected $model = Car::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'seller_id' => User::factory(),
            'brand' => fake()->randomElement([
                'Toyota', 'Honda', 'BMW', 'Mercedes', 'Audi',
                'Daihatsu', 'Suzuki', 'Mitsubishi', 'Nissan', 'Hyundai',
            ]),
            'model' => fake()->word(),
            'year' => fake()->year(),
            'color' => fake()->colorName(),
            'license_plate' => strtoupper(fake()->bothify('?-####-??')),
            'description' => fake()->paragraph(),
            'price' => fake()->numberBetween(10000000, 500000000), // In cents
            'mileage' => fake()->numberBetween(1000, 200000),
            'fuel_type' => fake()->randomElement(['petrol', 'diesel', 'hybrid', 'electric']),
            'transmission' => fake()->randomElement(['manual', 'automatic']),
            'images' => json_encode([
                'https://via.placeholder.com/600x400',
                'https://via.placeholder.com/600x400',
            ]),
            'features' => json_encode([
                'Air Conditioning',
                'Power Windows',
                'ABS',
                'Airbags',
            ]),
            'status' => 'available',
            'views_count' => fake()->numberBetween(0, 500),
            'listed_at' => fake()->dateTime(),
        ];
    }
}
