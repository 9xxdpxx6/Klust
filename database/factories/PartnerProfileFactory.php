<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PartnerProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->partner(),
            'company_name' => fake()->company(),
            'inn' => fake()->numerify('##########'),
            'address' => fake()->address(),
            'website' => fake()->url(),
            'description' => fake()->paragraph(3),
            'contact_person' => fake()->name(),
            'contact_phone' => fake()->phoneNumber(),
        ];
    }
}

