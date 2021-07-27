<?php

namespace App\Console\Commands;

use App\Models\Episode;
use App\Models\Host;
use App\Models\Subtopic;
use App\Models\Topic;
use App\Models\User;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class DatasetImporterCommand extends Command
{
    private static array $EPISODES = [];
    private static array $USERS = [];
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'datasets:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports datasets from YAML files';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->addArgument('location', InputArgument::OPTIONAL,
            'Importing datasets from yaml files', storage_path('datasets'));
        $this->addOption('skip-errors', null, InputOption::VALUE_NONE,
            'Continue importing datasets if a single dataset fails. Failing datasets will be ignored');
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // load datasets
        $location = $this->argument('location');
        $datasets = is_file($location) ? [$location] : array_values(array_filter(
        // list files in directory
            scandir($location),
            // only include .yml and .yaml files
            fn($filename) => substr($filename, -4) === '.yml' || substr($filename, -5) === '.yaml'
        ));

        foreach ($datasets as $filename) {
            if ($this->getOutput()->isVerbose())
                $this->getOutput()->writeln('<comment>Importing dataset ' . $filename . '</comment>');

            $dataset = yaml_parse_file(is_file($location) ? $location : $location . DIRECTORY_SEPARATOR . $filename);
            try {
                DB::beginTransaction();
                $this->import($dataset);
                DB::commit();
            } catch (Exception $ex) {
                $this->getOutput()->writeln('<error>Import failed for dataset ' . $filename . '</error>');

                if ($this->getOutput()->isVerbose())
                    $this->getOutput()->writeln('Reason: ' . $ex->getMessage());

                if ($this->getOutput()->isDebug()) {
                    $this->getOutput()->writeln('File: ' . $ex->getFile() . ':' . $ex->getLine());
                    $this->getOutput()->writeln('<comment>' . $ex->getTraceAsString() . '</comment>');
                }

                DB::rollBack();

                // stop import if not explicitly told otherwise
                if (!$this->option('skip-errors')) {
                    return 1;
                }

            }
        }

        return 0;
    }

    /**
     * @param array $dataset
     *
     * @return Episode
     * @throws Exception Thrown when episode has already additional data attached
     */
    private function import(array $dataset): Episode
    {
        $episode = $this->getEpisode($dataset);

        // do not allow to import datasets to already populated episodes
        // this could cause an exception later on and leave an inconsistent data state
        if ($episode->topics->count()) throw new Exception('Episode was already populated before');

        $episode->hosts()->detach();
        foreach ($this->getHosts($dataset) as $host) {
            $episode->hosts()->attach($host);
        }

        $episode = $this->setTopics($episode, $dataset);
        $episode->topics->each(fn(Topic $topic) => $this->setSubtopics($topic, $dataset));

        return $episode;
    }

    private function getDuration(string $duration): int
    {
        $interval = 0;
        $duration = explode(':', $duration);

        $interval += (int) array_pop($duration); // seconds
        $interval += ((int) array_pop($duration) ?? 0) * 60; // minutes
        $interval += ((int) array_pop($duration) ?? 0) * 60 * 60; // hours

        return $interval;
    }

    /**
     * Load episode, that this dataset is for.
     *
     * @param array $dataset
     *
     * @return Episode
     * @throws Exception
     */
    private function getEpisode(array $dataset): Episode
    {

        if (!array_key_exists('guid', $dataset)) {
            throw new Exception('Dataset has no episode GUID');
        }

        return static::$EPISODES[$dataset['guid']] ??= Episode::where(['guid' => $dataset['guid']])->firstOrFail();
    }

    private function getUser(string $username): User
    {
        return static::$USERS[$username] ??= User::where('username', $username)->firstOrFail();
    }

    /**
     * Get host entities defined in dataset. If a host does not exist yet, it will be created.
     *
     * @param array $dataset
     *
     * @return Host[]
     */
    private function getHosts(array $dataset): array
    {
        $hosts = [];

        foreach ($dataset['hosts'] as $host) {
            $hosts[] = Host::firstOrCreate([
                'name' => $host,
            ]);
        }

        return $hosts;
    }

    /**
     * @param Episode $episode
     * @param array $dataset
     *
     * @return Episode
     * @throws Exception
     */
    private function setTopics(Episode $episode, array $dataset): Episode
    {
        $topics = $dataset['topics'] ?? [];

        // normalize input
        $topics = array_map(function (array $data) use ($dataset, $episode) {

            // create topic
            $topic = new Topic([
                'name' => htmlspecialchars($data['name']),
                'ad' => (bool) $data['ad'],
                'community_contribution' => (bool) $data['community'],
                'start' => $this->getDuration($data['start']),
            ]);

            // set endpoint from dataset if it was defined
            $topic->end = $this->getDuration($data['end'] ?? '') ?: null;

            // add user relation
            $topic->user()->associate($this->getUser($dataset['username']));
            $topic->episode()->associate($episode);

            return $topic;
        }, $topics);

        // order topics by their start point
        usort($topics, fn($current, $before) => $current->start <=> $before->start);

        // add end timestamp
        foreach ($topics as $index => $topic) {
            // fill missing endpoints
            if ($next = $topics[$index+1] ?? false) {
                // set end point based on the net topics start point
                $topic->end ??= $next->start;
            } else {
                // last topic; set end point based on episode duration
                $topic->end ??= $this->getEpisode($dataset)->duration;
            }
        }

        $episode->topics()->saveMany($topics);
        return $episode->refresh();
    }

    /**
     * @param Topic $topic
     * @param array $dataset
     *
     * @return Topic
     */
    private function setSubtopics(Topic $topic, array $dataset): Topic
    {
        // collect subtopics from dataset
        $data = array_values(array_filter(
            $dataset['topics'] ?? [],
            fn(array $data) => $topic->name === htmlspecialchars($data['name'] ?? '')
        ))[0] ?? [];

        // build subtopic models
        $subtopics = array_map(function (string $subtopic) use ($dataset, $topic) {
            $subtopic = new Subtopic([
                'name' => htmlspecialchars($subtopic),
            ]);
            $subtopic->user()->associate($this->getUser($dataset['username']));
            $subtopic->topic()->associate($topic);
            return $subtopic;
        }, $data['subtopics'] ?? []);

        // persist subtopics
        $topic->subtopics()->saveMany($subtopics);

        return $topic->refresh();
    }
}

