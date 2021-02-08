<?php

namespace App\Jobs;

use App\Models\PodcastEpisode;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Console\OutputStyle;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Lukaswhite\PodcastFeedParser\Artwork;
use Lukaswhite\PodcastFeedParser\Episode;
use Lukaswhite\PodcastFeedParser\Exceptions\InvalidXmlException;
use Lukaswhite\PodcastFeedParser\Parser;

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
     * @param Client $client
     * @param Parser $parser
     *
     * @return void
     * @throws GuzzleException
     * @throws InvalidXmlException
     * @throws Exception
     */
    public function handle(Client $client, Parser $parser)
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
         * @var Episode $feedItem
         */
        foreach ($feedItems->getIterator() as $feedItem) {

            // search for existing episodes with the same uuid
            /**
             * @var $episode PodcastEpisode
             */
            $episode = PodcastEpisode::firstOrNew([
                'uuid' => $feedItem->getGuid(),
            ]);

            // only import if episode wasn't already imported
            if (!$episode->exists) {

                // map feed to model
                $episode->fill([
                    'uuid' => $feedItem->getGuid(),
                    'episode_number' => $feedItem->getEpisodeNumber(),
                    'title' => $feedItem->getTitle(),
                    'subtitle' => $feedItem->getSubtitle(),
                    'description' => $feedItem->getDescription(),
                    'image' => $feedItem->getArtwork() instanceof Artwork ? $feedItem->getArtwork()->getUri() : null,
                    'type' => $feedItem->getType(),
                    'duration' => $feedItem->getDuration(),
                    'explicit' => $feedItem->getExplicit() === 'no' ? false : true,
                    'date_published' => $feedItem->getPublishedDate(),
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
