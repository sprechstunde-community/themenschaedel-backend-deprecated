<?php

namespace App\Jobs;

use App\Models\PodcastEpisode;
use Carbon\Carbon;
use Feed;
use FeedException;
use Illuminate\Bus\Queueable;
use Illuminate\Console\OutputStyle;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportPodcastEpisodes implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private ?OutputStyle $output;

    /**
     * Create a new job instance.
     *
     * @param OutputStyle|null $output Used to print progress bar if set.
     */
    public function __construct(?OutputStyle $output = null)
    {
        $this->output = $output;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws FeedException
     */
    public function handle()
    {
        // download rss feed
        $feed = Feed::loadRss(config('services.podcasts.rss_feed'));
        $feedItems = $feed->item ?? [];

        // show progress
        if ($this->output) {
            $progress = $this->output->createProgressBar(count($feedItems));
        }


        foreach ($feedItems as $feedItem) {

            // search for existing episodes with the same uuid
            /**
             * @var $episode PodcastEpisode
             */
            $episode = PodcastEpisode::firstOrNew([
                'uuid' => $feedItem->guid,
            ]);

            // only import if episode wasn't already imported
            if (!$episode->exists) {

                // map feed to model
                $episode->fill([
                    'uuid' => (string) $feedItem->guid,
                    'episode_number' => (int) $feedItem->{'itunes:episode'},
                    'title' => (string) $feedItem->title,
                    'subtitle' => (string) $feedItem->subtitle,
                    'description' => (string) $feedItem->description,
                    'image' => (string) $feedItem->{'itunes:image'},
                    'type' => (string) $feedItem->{'itunes:episodeType'},
                    'duration' => (string) $feedItem->{'itunes:duration'},
                    'explicit' => $feedItem->{'itunes:explicit'} === 'no' ? false : true,
                    'date_published' => new Carbon($feedItem->pubDate),
                ]);

                // persist model in database
                $success = $episode->save();

                // show progress
                if ($progress) {
                    $progress->advance((int) $success);
                }
            }
        }

        if ($progress) {
            $progress->finish();
        }
    }
}
