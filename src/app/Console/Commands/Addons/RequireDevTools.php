<?php

namespace Backpack\CRUD\app\Console\Commands\Addons;

use Illuminate\Console\Command;

class RequireDevTools extends Command
{
    use \Backpack\CRUD\app\Console\Commands\Traits\PrettyCommandOutput;
    use \Backpack\CRUD\app\Console\Commands\Traits\AddonsHelper;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backpack:require:devtools
                                {--debug} : Show process output or not. Useful for debugging.';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Require Backpack DevTools on dev';

    /**
     * Backpack addons install attribute.
     *
     * @var array
     */
    public static $addon = [
        'name' => 'DevTools',
        'description' => [
            'Helps generate models, migrations, operations and CRUDs',
        ],
        'path' => 'vendor/backpack/devtools',
        'command' => 'backpack:require:devtools',
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
            $this->composerRequire('backpack/devtools', ['--dev', '--with-all-dependencies']);
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

        // manually include the command in the run-time
        if (! class_exists(\Backpack\DevTools\Console\Commands\InstallDevTools::class)) {
            include base_path('vendor/backpack/devtools/src/Console/Commands/InstallDevTools.php');
        }

        $this->call(\Backpack\DevTools\Console\Commands\InstallDevTools::class);
    }

    public function isInstalled()
    {
        return file_exists(self::$addon['path'].'/composer.json');
    }
}
