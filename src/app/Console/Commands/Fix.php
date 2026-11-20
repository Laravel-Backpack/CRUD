<?php

namespace Backpack\CRUD\app\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class Fix extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backpack:fix';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix known Backpack issues.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->fixErrorViews();

        if ($this->confirm('[SUGGESTION] Would you like to publish updated JS & CSS dependencies to public/packages?', false)) {
            Artisan::call('vendor:publish', [
                '--provider' => 'Backpack\CRUD\BackpackServiceProvider',
                '--tag' => 'assets',
                '--force' => 'true',
            ]);
            $this->info('Published latest CSS and JS assets to your public/packages directory.');
        }
    }

    private function fixErrorViews()
    {
        $errorsDirectory = base_path('resources/views/errors');

        $this->line('Checking error views...');

        // check if the `resources/views/errors` directory exists
        if (! is_dir($errorsDirectory)) {
            $this->info('Your error views are not vulnerable. Nothing to do here.');

            return;
        }

        $views = scandir($errorsDirectory);
        $views = array_filter($views, function ($file) {
            // eliminate ".", ".." and any hidden files like .gitignore or .gitkeep
            return substr($file, 0, 1) != '.';
        });

        // check if there are actually views inside the directory
        if (! count($views)) {
            $this->info('Your error views are not vulnerable. Nothing to do here.');

            return;
        }

        $autofixed = true;
        foreach ($views as $key => $view) {
            $contents = file_get_contents($errorsDirectory.'/'.$view);

            // does it even work with exception messages?
            if (strpos($contents, '->getMessage()') == false) {
                continue;
            }

            // does it already escape the exception message?
            if (strpos($contents, 'e($exception->getMessage())') !== false) {
                $this->info($view.' was ok.');
                continue;
            }

            // cover the most likely scenario, where the file has not been edited at all
            $new_contents = str_replace('$exception->getMessage()?$exception->getMessage():$default_error_message', '$exception->getMessage()?e($exception->getMessage()):$default_error_message', $contents);

            if ($new_contents != $contents) {
                file_put_contents($errorsDirectory.'/'.$view, $new_contents);
                $this->warn($view.' has been fixed.');
                continue;
            }

            $this->error($view.' could not be fixed automatically.');
            $autofixed = false;
        }

        if ($autofixed == false) {
            $this->error('Some error views could not be fixed automatically. Please look inside your "resources/views/errors" directory and make sure exception messages are escaped before outputting. It should be e($exception->getMessage()) instead of $exception->getMessage(). Alternatively, outputting should be done using {{ }} instead of {!! !!}');
        }
    }
}
