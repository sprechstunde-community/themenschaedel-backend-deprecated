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
                    $progress->advance((int)$success);
                }
            }
        }

        if ($progress) {
            $progress->finish();
        }
    }

    /** @throws Exception Failed to parse timestamp */
    private function calculateTimespan(string $time): int
    {
        // parse timestamp
        $segments = explode(':', $time);
        // start with lowest unit (seconds)
        $segments = array_reverse($segments);

        if (count($segments) > 3) {
            throw new Exception('Timestamps with segments above `hours` is not supported');
        }

        $time = 0;
        $multiplier = 1;
        foreach ($segments as $segment) {
            $time += $multiplier * (int)$segment;
            $multiplier *= 60;
        }

        return $time;
    }
}
