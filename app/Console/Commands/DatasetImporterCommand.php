<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class DatasetImporterCommand extends Command
{
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
    protected $description = 'Imports datasets from yaml files';

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
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // load datasets
        $datasets = array_values(array_filter(
        // list files in directory
            scandir($this->argument('location')),
            // only include .yml and .yaml files
            fn($filename) => substr($filename, -4) === '.yml' || substr($filename, -5) === '.yaml'
        ));

        foreach ($datasets as $dataset) {
            if ($this->getOutput()->isVerbose())
                $this->getOutput()->writeln('<comment>Importing Dataset ' . $dataset);

            // TODO import dataset
        }

        return 0;
    }
}
