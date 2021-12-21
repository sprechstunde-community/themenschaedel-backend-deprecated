<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'username' => $this->faker->userName,
            'name' => $this->faker->boolean(40) ? $this->faker->name : null,
            'description' => $this->faker->boolean(10) ? $this->faker->name : null,
            'email' => $this->faker->unique()->safeEmail,
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * User hasn't the moderator role
     */
    public function contributor(): UserFactory
    {
        return $this->state(fn(array $attributes) => ['is_moderator' => false]);
    }

    /**
     * User has the moderator role
     */
    public function moderator(): UserFactory
    {
        return $this->state(fn(array $attributes) => ['is_moderator' => true]);
    }
}
