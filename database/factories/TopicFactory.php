<?php

namespace Database\Factories;

use App\Models\Episode;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use InvalidArgumentException;

class TopicFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Topic::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $episode = $this->getEpisode();
        $max = $episode->duration;
        $start = $this->faker->numberBetween(0, $max);
        $end = $this->faker->numberBetween($start, $max);
        $byCommunity = $this->faker->boolean(75);

        return [
            'episode_id' => $episode->id,
            'user_id' => $this->getUser()->id,
            'name' => $this->faker->text(),
            'start' => $start,
            'end' => $end,
            'ad' => $byCommunity ? false : $this->faker->boolean(10),
            'community_contribution' => $byCommunity,
        ];
    }

    private function getEpisode(): Episode
    {
        try {
            return Episode::all()->random();
        } catch (InvalidArgumentException $exception) {
            throw new InvalidArgumentException("No existing podcast episode found. " .
                "Please add new episodes first. Needed for relations.");
        }
    }

    private function getUser(): User
    {
        try {
            return User::all()->random();
        } catch (InvalidArgumentException $exception) {
            throw new InvalidArgumentException("No existing user found. " .
                "Please add new user first. Needed for relations.");
        }
    }
}
