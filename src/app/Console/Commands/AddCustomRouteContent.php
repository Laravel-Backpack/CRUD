<?php

namespace Backpack\CRUD\app\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class AddCustomRouteContent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backpack:add-custom-route
                                {code : HTML/PHP code that registers a route. Use either single quotes or double quotes. Never both. }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add HTML/PHP code to the routes/backpack/custom.php file';

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
     * @return mixed
     */
    public function handle()
    {
        $path = 'routes/backpack/custom.php';
        $disk_name = config('backpack.base.root_disk_name');
        $disk = Storage::disk($disk_name);
        $code = $this->argument('code');

        if ($disk->exists($path)) {
            $old_file_path = $disk->path($path);

            // insert the given code before the file's last line
            $file_lines = file($old_file_path, FILE_IGNORE_NEW_LINES);

            // if the code already exists in the file, abort
            if ($this->getLastLineNumberThatContains($code, $file_lines)) {
                return $this->comment('Route already existed.');
            }

            $end_line_number = $this->customRoutesFileEndLine($file_lines);

            array_splice($file_lines, $end_line_number, 0, '    '.$code);
            $new_file_content = implode(PHP_EOL, $file_lines);

            if ($disk->put($path, $new_file_content)) {
                $this->info('Successfully added code to '.$path);
            } else {
                $this->error('Could not write to file: '.$path);
            }
        } else {
            Artisan::call('vendor:publish', ['--provider' => 'Backpack\CRUD\BackpackServiceProvider', '--tag' => 'custom_routes']);

            $this->handle();
        }
    }

    private function customRoutesFileEndLine($file_lines)
    {
        // in case the last line has not been modified at all
        $end_line_number = array_search('}); // this should be the absolute last line of this file', $file_lines);

        if ($end_line_number) {
            return $end_line_number;
        }

        // otherwise, in case the last line HAS been modified
        // return the last line that has an ending in it
        $possible_end_lines = array_filter($file_lines, function ($k) {
            return strpos($k, '}') !== false;
        });

        if ($possible_end_lines) {
            end($possible_end_lines);
            $end_line_number = key($possible_end_lines);

            return $end_line_number;
        }
    }

    /**
     * Parse the given file stream and return the line number where a string is found.
     *
     * @param  string  $needle  The string that's being searched for.
     * @param  array  $haystack  The file where the search is being performed.
     * @return bool|int The last line number where the string was found. Or false.
     */
    private function getLastLineNumberThatContains($needle, $haystack)
    {
        $matchingLines = array_filter($haystack, function ($k) use ($needle) {
            return strpos($k, $needle) !== false;
        });

        if ($matchingLines) {
            return array_key_last($matchingLines);
        }

        return false;
    }
}
