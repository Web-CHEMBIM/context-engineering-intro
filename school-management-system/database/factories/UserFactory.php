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
        $gender = fake()->randomElement(['male', 'female', 'other']);
        $firstName = fake()->firstName($gender === 'other' ? null : $gender);
        $lastName = fake()->lastName();
        
        return [
            'name' => $firstName . ' ' . $lastName,
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => fake()->optional(0.8)->dateTimeBetween('-1 year', 'now'),
            'password' => static::$password ??= Hash::make('password'),
            'phone' => fake()->optional(0.7)->numerify('+1##########'),
            'date_of_birth' => fake()->dateTimeBetween('-70 years', '-16 years'),
            'gender' => $gender,
            'address' => fake()->optional(0.6)->address(),
            'profile_photo' => null,
            'is_active' => fake()->boolean(95), // 95% chance of being active
            'last_login_at' => fake()->optional(0.8)->dateTimeBetween('-30 days', 'now'),
            'remember_token' => Str::random(10),
        ];
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

    /**
     * Create a student user.
     */
    public function student(): static
    {
        return $this->state(fn (array $attributes) => [
            'date_of_birth' => fake()->dateTimeBetween('-25 years', '-5 years'),
        ]);
    }

    /**
     * Create a teacher user.
     */
    public function teacher(): static
    {
        return $this->state(fn (array $attributes) => [
            'date_of_birth' => fake()->dateTimeBetween('-65 years', '-22 years'),
            'is_active' => true,
        ]);
    }

    /**
     * Create an admin user.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'date_of_birth' => fake()->dateTimeBetween('-60 years', '-25 years'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Create an inactive user.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'last_login_at' => fake()->optional(0.3)->dateTimeBetween('-6 months', '-1 month'),
        ]);
    }
}
