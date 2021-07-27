<?php

namespace Database\Factories;

use App\Models\Episode;
use Illuminate\Database\Eloquent\Factories\Factory;

class EpisodeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Episode::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'guid' => $this->faker->uuid,
            'episode_number' => $this->faker->numberBetween(1, 100),
            'title' => $this->faker->text(32),
            'subtitle' => $this->faker->text(64),
            'description' => $this->faker->text(512),
            'image' => $this->faker->imageUrl(),
            'duration' => $this->faker->numberBetween(512, 8192),
            'type' => $this->faker->boolean(85),
            'explicit' => $this->faker->boolean(10),
            'published_at' => $this->faker->dateTime(),

        ];
    }
}

