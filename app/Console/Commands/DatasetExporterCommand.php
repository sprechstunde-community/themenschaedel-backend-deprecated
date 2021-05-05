<?php

namespace App\Console\Commands;

use App\Models\Episode;
use App\Models\Topic;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class DatasetExporterCommand
 *
 * @author Vincent Neubauer <v.neubauer@darlor.de>
 * @package App\Console\Commands
 */
class DatasetExporterCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'datasets:export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exports dataset and stores them as a YAML file';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Add command arguments
        $this->addArgument('location', InputArgument::OPTIONAL,
            'Import datasets as YAML files into this directory', storage_path('datasets'));
        $this->addOption('prefix', 'p', InputOption::VALUE_OPTIONAL,
            'Filename prefix to use when exporting datasets', 'episode');
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // iterate over database in chunks of 25 episodes, aggregate all needed properties and export each of them
        Episode::with(['hosts', 'topics.subtopics', 'topics.user'])->orderBy('episode_number')
            ->chunk(25, fn(Collection $episodes) => $episodes->each(fn(Episode $episode) => $this->export($episode)));
        return 0;
    }

    protected function export(Episode $episode): void
    {
        $dataset = [
            'guid' => $episode->guid,
            'title' => $episode->title,
        ];

        foreach ($episode->topics as $topic) {
            /** @var Topic $topic */

            $dataset['topics'][] = [
                'name' => htmlspecialchars_decode($topic->name),
                'start' => gmdate("H:i:s", $topic->start),
                'end' => gmdate("H:i:s", $topic->end),
                'ad' => $topic->ad ? 'true' : 'false',
                'community' => $topic->community_contribution ? 'true' : 'false',
                'subtopics' => $topic->subtopics()->get('name')->toArray(),
            ];
        }

        $filename = $this->argument('location') . DIRECTORY_SEPARATOR . $this->option('prefix')
            . '-' . $episode->episode_number . '.yml';

        if ($this->output->isVerbose())
            $this->output->writeln('Exporting ' . basename($filename));

        yaml_emit_file($filename, $dataset, YAML_UTF8_ENCODING);
    }
}
