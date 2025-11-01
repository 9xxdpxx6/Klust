<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kubgtu_id' => fake()->boolean(30) ? fake()->unique()->numerify('STU#######') : null,
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'avatar' => fake()->boolean(70) ? fake()->imageUrl(200, 200, 'people') : null,
            'course' => fake()->boolean(80) ? fake()->numberBetween(1, 6) : null,
            'remember_token' => Str::random(10),
        ];
    }

    public function student(): static
    {
        return $this->state(fn (array $attributes) => [
            'course' => fake()->numberBetween(1, 6),
        ]);
    }

    public function partner(): static
    {
        return $this->state(fn (array $attributes) => [
            'course' => null,
            'kubgtu_id' => null,
        ]);
    }

    public function teacher(): static
    {
        return $this->state(fn (array $attributes) => [
            'course' => null,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
