<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['repaint', 'general']);

        return [
            'name' => match ($type) {
                'repaint' => fake()->randomElement([
                    'Full Body Repaint',
                    'Partial Repaint',
                    'Ceramic Coating',
                    'Premium Paint Protection',
                    'Color Correction',
                ]),
                'general' => fake()->randomElement([
                    'Oil Change',
                    'Brake Service',
                    'Tire Replacement',
                    'Car Wash & Detailing',
                    'Engine Maintenance',
                ]),
            },
            'description' => fake()->paragraph(),
            'type' => $type,
            'price' => fake()->numberBetween(100000, 5000000), // In cents
            'duration_minutes' => fake()->randomElement([30, 60, 90, 120, 240]),
            'image_url' => 'https://via.placeholder.com/400x300',
            'included_features' => json_encode([
                'Quality Guarantee',
                'Free Inspection',
                'Professional Team',
            ]),
            'is_active' => true,
        ];
    }
}
