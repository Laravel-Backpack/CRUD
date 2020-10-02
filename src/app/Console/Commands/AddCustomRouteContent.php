<?php

namespace Backpack\CRUD\app\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

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
                return $this->info('Route already exists!');
            }

            $end_line_number = $this->customRoutesFileEndLine($file_lines);
            $file_lines[$end_line_number + 1] = $file_lines[$end_line_number];
            $file_lines[$end_line_number] = '    '.$code;
            $new_file_content = implode(PHP_EOL, $file_lines);

            if ($disk->put($path, $new_file_content)) {
                $this->info('Successfully added code to '.$path);
            } else {
                $this->error('Could not write to file: '.$path);
            }
        } else {
            $command = 'php artisan vendor:publish --provider="Backpack\Base\BaseServiceProvider" --tag=custom_routes';

            $process = new Process($command, null, null, null, 300, null);

            $process->run(function ($type, $buffer) {
                if (Process::ERR === $type) {
                    $this->line($buffer);
                } else {
                    $this->line($buffer);
                }
            });

            // executes after the command finishes
            if (! $process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $this->handle();
        }
    }

    /**
     * Get the line before which routes should be inserted.
     *
     * @param  array $file_lines The file on which search is being performed.
     * @return int               The line before which the routes should be inserted.
     */
    private function customRoutesFileEndLine($file_lines)
    {
        // in case there's a beginning and end comment
        $lineWithEndComment = $this->getLastLineNumberThatContains('// end of generated Backpack routes; DO NOT delete or modify this comment;', $file_lines);
        // in case the last line has not been modified at all
        $lineWithNormalEnding = $this->getLastLineNumberThatContains('}); // this should be the absolute last line of this file', $file_lines);
        // the last line that has a closure ending in it
        $lineWithClosureEnding = $this->getLastLineNumberThatContains('});', $file_lines);

        return  $lineWithEndComment ??
                $lineWithNormalEnding ??
                $lineWithClosureEnding ??
                count($file_lines) - 1 ?? 0; // if the ending is still not clear, assume it's ok to use the last line as the ending
    }

    /**
     * Parse the given file stream and return the line number where a string is found.
     *
     * @param  string $needle   The string that's being searched for.
     * @param  array $haystack  The file where the search is being performed.
     * @return bool|int         The last line number where the string was found. Or false.
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

    /**
     * Parse the given file stream and return the line number where a string is found.
     *
     * @param  string $needle   The string that's being searched for.
     * @param  array $haystack  The file where the search is being performed.
     * @return bool|int         The last line number where the string was found. Or false.
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
