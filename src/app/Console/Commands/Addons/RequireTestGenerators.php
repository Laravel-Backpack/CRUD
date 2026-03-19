<?php

namespace Backpack\CRUD\app\Console\Commands\Addons;

use Illuminate\Console\Command;

class RequireTestGenerators extends Command
{
    use \Backpack\CRUD\app\Console\Commands\Traits\PrettyCommandOutput;
    use \Backpack\CRUD\app\Console\Commands\Traits\AddonsHelper;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backpack:require:test-generators
                                {--debug} : Show process output or not. Useful for debugging.';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Require Backpack TestGenerators on dev';

    /**
     * Backpack addons install attribute.
     *
     * @var array
     */
    public static $addon = [
        'name' => 'TestGenerators',
        'description' => [
            'Generates tests for your Backpack CRUDs',
        ],
        'path' => 'vendor/backpack/test-generators',
        'command' => 'backpack:require:test-generators',
    ];

    /**
     * Execute the console command.
     *
     * @return mixed Command-line output
     */
    public function handle()
    {
        // Check if it is installed
        if ($this->isInstalled()) {
            $this->newLine();
            $this->line(sprintf('  %s was already installed', self::$addon['name']), 'fg=red');
            $this->newLine();

            return;
        }

        $this->newLine();
        $this->infoBlock('Connecting to the Backpack add-on repository');

        // Check if repositories exists
        $this->composerRepositories();

        // Check for authentication
        $this->checkForAuthentication();

        $this->newLine();
        $this->progressBlock($this->description);

        // Require package
        try {
            $this->composerRequire('backpack/test-generators', ['--dev', '--with-all-dependencies']);
        } catch (\Throwable $e) {
            $this->errorProgressBlock();
            $this->line('  '.$e->getMessage(), 'fg=red');
            $this->newLine();

            return;
        }

        // Display general error in case it failed
        if (! $this->isInstalled()) {
            $this->errorProgressBlock();
            $this->note('For further information please check the log file.');
            $this->note('You can also follow the manual installation process documented in https://backpackforlaravel.com/addons/');
            $this->newLine();

            return;
        }

        // Finish
        $this->closeProgressBlock();
        $this->newLine();
    }

    public function isInstalled()
    {
        return file_exists(self::$addon['path'].'/composer.json');
    }
}
