<?php

namespace Database\Factories;

use App\Models\Claim;
use App\Models\Episode;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClaimFactory extends Factory
{
    private static Collection $episodes;
    private static Collection $users;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Claim::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // load all available episodes and orders in a random order
        static::$episodes ??= Episode::doesntHave('claimed')->get()
            ->sort(fn() => $this->faker->randomNumber());
        static::$users ??= User::doesntHave('claim')->get()
            ->sort(fn() => $this->faker->randomNumber());

        // get one episode / user and remove it from the list
        $episode = static::$episodes->pop();
        $user = static::$users->pop();

        // generate new claim
        return [
            'episode_id' => $episode->getKey(),
            'user_id' => $user->getKey(),
            'claimed_at' => $this->faker->dateTime(),
        ];
    }
}

