<?php

namespace App\Console\Commands;

use App\Jobs\ImportPodcastEpisodes;
use Illuminate\Console\Command;

class PodcastEpisodesImporter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'podcast:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports latest episodes from configured podcast feed';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->output->isVerbose()) {
            dispatch_now(new ImportPodcastEpisodes($this->output));
        } else {
            dispatch_now(new ImportPodcastEpisodes());
        }
        return 0;
    }
}
