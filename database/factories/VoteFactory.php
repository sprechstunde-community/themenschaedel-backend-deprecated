<?php

namespace Database\Factories;

use App\Models\Episode;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Factories\Factory;

class VoteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Vote::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $user = User::all()->random();
        $episode = Episode::doesntHave('votes')->get();
        return [
            'episode_id' => $episode->random()->id,
            'user_id' => $user->id,
            'positive' => $this->faker->boolean(75),
        ];
    }
}

