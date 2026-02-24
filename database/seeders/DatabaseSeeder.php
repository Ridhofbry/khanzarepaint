<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\Voucher;
use App\Models\FAQ;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed services
        Service::factory(6)->create();

        // Seed vouchers
        Voucher::factory(5)->create();

        // Seed FAQs
        FAQ::create([
            'question' => 'How do I book a service?',
            'answer' => 'You can book a service by clicking on the "Book Now" button and selecting your preferred service and time slot.',
            'category' => 'booking',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        FAQ::create([
            'question' => 'What is the membership program?',
            'answer' => 'Our membership program offers tiered benefits (Bronze, Silver, Gold) based on your service frequency, providing you with exclusive discounts.',
            'category' => 'membership',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        FAQ::create([
            'question' => 'How can I sell my car?',
            'answer' => 'To sell your car on our garage marketplace, sign up as a garage owner, upload your car details and photos, and your listing will go live.',
            'category' => 'garage',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        FAQ::create([
            'question' => 'What payment methods do you accept?',
            'answer' => 'We accept credit cards, bank transfers, and e-wallets for all transactions.',
            'category' => 'payment',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        FAQ::create([
            'question' => 'Is there a warranty on services?',
            'answer' => 'Yes, all our services come with a satisfaction guarantee. If you\'re not happy, we\'ll make it right.',
            'category' => 'general',
            'sort_order' => 1,
            'is_active' => true,
        ]);
    }
}
