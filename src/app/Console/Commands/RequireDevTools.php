<?php

namespace Backpack\CRUD\app\Console\Commands;

use File;
use Illuminate\Console\Command;
use Str;
use Symfony\Component\Process\Process;

class RequireDevTools extends Command
{
    use \Backpack\CRUD\app\Console\Commands\Traits\PrettyCommandOutput;

    protected $progressBar;

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
    protected $description = 'Require Backpack DevTools on dev.';

    /**
     * Execute the console command.
     *
     * @return mixed Command-line output
     */
    public function handle()
    {
        $this->progressBar = $this->output->createProgressBar(15);
        $this->progressBar->minSecondsBetweenRedraws(0);
        $this->progressBar->maxSecondsBetweenRedraws(120);
        $this->progressBar->setRedrawFrequency(1);

        $this->progressBar->start();

        $this->info(' Requiring DevTools. Please wait...');
        $this->progressBar->advance();

        // Check if auth exists
        $details = null;
        $process = new Process(['composer', 'config', 'http-basic.backpackforlaravel.com']);
        $process->run(function ($type, $buffer) use (&$details) {
            if ($type !== Process::ERR && $buffer !== '') {
                $details = json_decode($buffer);
            } elseif (File::exists('auth.json')) {
                $details = json_decode(File::get('auth.json'), true)['http-basic']['backpackforlaravel.com'] ?? false;
            }
            $this->progressBar->advance();
        });

        // Create an auth.json file
        if (! $details) {
            $this->info(' Creating auth.json file with your authentication token');

            $this->line(' (Find your access token details on https://backpackforlaravel.com/user/tokens)');
            $username = $this->ask('Access token username');
            $password = $this->ask('Access token password');

            $process = new Process(['composer', 'config', 'http-basic.backpackforlaravel.com', $username, $password]);
            $process->run(function ($type, $buffer) use ($username, $password) {
                if ($type === Process::ERR) {
                    // Fallback
                    $authFile = [
                        'http-basic' => [
                            'backpackforlaravel.com' => [
                                'username' => $username,
                                'password' => $password,
                            ],
                        ],
                    ];

                    if (File::exists('auth.json')) {
                        $currentFile = json_decode(File::get('auth.json'), true);
                        if (! ($currentFile['http-basic']['backpackforlaravel.com'] ?? false)) {
                            $authFile = array_merge_recursive($authFile, $currentFile);
                        }
                    }

                    File::put('auth.json', json_encode($authFile, JSON_PRETTY_PRINT));
                }
                $this->progressBar->advance();
            });
        }

        // Check if repositories exists
        $details = null;
        $process = new Process(['composer', 'config', 'repositories.backpack/devtools']);
        $process->run(function ($type, $buffer) use (&$details) {
            if ($type !== Process::ERR && $buffer !== '') {
                $details = json_decode($buffer);
            } else {
                $details = json_decode(File::get('composer.json'), true)['repositories']['backpack/devtools'] ?? false;
            }
            $this->progressBar->advance();
        });

        // Create repositories
        if (! $details) {
            $this->info(' Creating repositories entry in composer.json');

            $process = new Process(['composer', 'config', 'repositories.backpack/devtools', 'vcs', 'https://github.com/laravel-backpack/DevTools']);
            $process->run(function ($type, $buffer) {
                if ($type === Process::ERR) {
                    // Fallback
                    $composerJson = Str::of(File::get('composer.json'));

                    $currentRepositories = json_decode($composerJson, true)['repositories'] ?? false;

                    $repositories = Str::of(json_encode([
                        'repositories' => [
                            'backpack/devtools' => [
                                'type' => 'vcs',
                                'url' => 'https://github.com/laravel-backpack/DevTools',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

                    $composerJson = $currentRepositories
                        // Replace current repositories
                        ? preg_replace('/"repositories":\s{\r?\n/', $repositories->match("/\{\n\s+([\s\S]+)\n\s{4}\}/m")->append(",\n"), $composerJson)
                        // Append to the end of the file
                        : preg_replace("/\r?\n}/", $repositories->replaceFirst('{', ','), $composerJson);

                    File::put('composer.json', $composerJson);
                }
                $this->progressBar->advance();
            });
        }

        // Require package
        $process = new Process(['composer', 'require', '--dev', 'backpack/devtools']);
        $process->run(function ($type, $buffer) {
            $this->progressBar->advance();
        });

        // Finish
        $this->progressBar->finish();
        $this->info(' DevTools is now required.');

        // DevTools inside installer
        $this->info('');
        $this->info(' Now running the DevTools installation command.');
        $this->call('backpack:devtools:install');
    }
}
