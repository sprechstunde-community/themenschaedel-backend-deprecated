<?php

namespace Database\Seeders;

use App\Jobs\PodcastEpisodesImporterJob;
use App\Models\Claim;
use App\Models\Episode;
use App\Models\Flag;
use App\Models\Subtopic;
use App\Models\Topic;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $stdout = $this->command->getOutput();

        // Import all podcast episodes from feed
        dispatch_now(new PodcastEpisodesImporterJob($stdout));
        $episodeCount = Episode::all()->count();
        if ($stdout->isVerbose()) $stdout->text(sprintf('Imported %d episodes from rss feed', $episodeCount));

        // Generate some users
        $userCount = 50;
        if ($stdout->isVerbose()) $stdout->text(sprintf('Generating %d users', $userCount));
        User::factory($userCount)->create();

        // Generate topics to episodes with each up to 5 subtopics
        $topicCount = $episodeCount * 2;
        if ($stdout->isVerbose()) $stdout->text(sprintf('Generating %d topics with subtopics', $topicCount));
        Topic::factory($episodeCount)
            ->has(Subtopic::factory(random_int(0, 5)))
            ->create();

        // Generate random votes for each episode
        $voteCount = $episodeCount * 3;
        if ($stdout->isVerbose()) $stdout->text(sprintf('Generating %d votes', $voteCount));
        Vote::factory($voteCount)->create();

        // Generate flags for some episodes
        $flagCount = ceil($episodeCount > 20 ? $episodeCount / 10 : $episodeCount / 2);
        if ($stdout->isVerbose()) $stdout->text(sprintf('Flagging %d episodes', $flagCount));
        Flag::factory($flagCount)->create();

        // Claiming episodes
        $claimCount = ceil($userCount / 3);
        if ($stdout->isVerbose()) $stdout->text(sprintf('Claiming %d episodes', $claimCount));
        Claim::factory($claimCount);
    }
}
