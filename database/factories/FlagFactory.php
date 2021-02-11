<?php

namespace Database\Factories;

use App\Models\Episode;
use App\Models\Flag;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class FlagFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Flag::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $user = User::all()->random();
        $episodes = Episode::doesntHave('flags')->get();

        return [
            'episode_id' => $episodes->random()->id,
            'user_id' => $user->id,
            'reason' => $this->faker->text,
        ];
    }
}

