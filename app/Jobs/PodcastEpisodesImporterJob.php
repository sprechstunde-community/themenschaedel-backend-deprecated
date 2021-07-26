<?php

namespace App\Jobs;

use App\Models\Episode;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Console\OutputStyle;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Lukaswhite\PodcastFeedParser\Artwork;
use Lukaswhite\PodcastFeedParser\Episode as PodcastEpisode;
use Lukaswhite\PodcastFeedParser\Exceptions\InvalidXmlException;
use Lukaswhite\PodcastFeedParser\Parser;

class PodcastEpisodesImporterJob implements ShouldQueue
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
     * @param Client $client
     * @param Parser $parser
     *
     * @return void
     * @throws GuzzleException
     * @throws InvalidXmlException
     * @throws Exception
     */
    public function handle(ClientInterface $client, Parser $parser)
    {
        // download rss feed
        $response = $client->get(config('services.podcasts.rss_feed'));
        $feed = $parser->setContent($response->getBody()->getContents())->run();
        $feedItems = $feed->getEpisodes();
        $progress = null;

        // show progress
        if ($this->output) {
            $progress = $this->output->createProgressBar(count($feedItems));
        }

        /**
         * @var PodcastEpisode $feedItem
         */
        foreach ($feedItems->getIterator() as $feedItem) {

            // search for existing episodes with the same uuid
            /**
             * @var $episode Episode
             */
            $episode = Episode::firstOrNew([
                'guid' => $feedItem->getGuid(),
            ]);

            // only import if episode wasn't already imported
            if (!$episode->exists) {

                // map feed to model
                $episode->fill([
                    'guid' => $feedItem->getGuid(),
                    'episode_number' => $feedItem->getEpisodeNumber(),
                    'title' => $feedItem->getTitle(),
                    'subtitle' => $feedItem->getSubtitle(),
                    'description' => $feedItem->getDescription(),
                    'image' => $feedItem->getArtwork() instanceof Artwork ? $feedItem->getArtwork()->getUri() : null,
                    'type' => $feedItem->getType(),
                    'duration' => $this->calculateTimespan($feedItem->getDuration()),
                    'explicit' => $feedItem->getExplicit() === 'no' ? false : true,
                    'published_at' => $feedItem->getPublishedDate(),
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

    private function calculateTimespan(string $time): int
    {
        // Parse input string
        preg_match('/(?<hours>\d{2}):(?<minutes>\d{2}):(?<seconds>\d{2})/', $time, $matches);

        $time = (int) $matches['seconds']; // Add seconds
        $time += 60 * (int) $matches['minutes']; // Add minutes as Seconds
        $time += 60 * 60 * (int) $matches['hours']; // Add hours as Seconds

        return $time;
    }
}
