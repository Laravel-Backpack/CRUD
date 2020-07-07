<?php

namespace Backpack\CRUD\app\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class AddSidebarContent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backpack:add-sidebar-content
                                {code : HTML/PHP code that shows the sidebar item. Use either single quotes or double quotes. Never both. }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add HTML/PHP code to the Backpack sidebar_content file';

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
        $path = 'resources/views/vendor/backpack/base/inc/sidebar_content.blade.php';
        $disk_name = config('backpack.base.root_disk_name');
        $disk = Storage::disk($disk_name);
        $code = $this->argument('code');

        if ($disk->exists($path)) {
            $contents = $disk->get($path);
            $file_lines = file($disk->path($path), FILE_IGNORE_NEW_LINES);

            if ($this->getLastLineNumberThatContains($code, $file_lines)) {
                return $this->info('Sidebar item already exists!');
            }

            if ($disk->put($path, $contents.PHP_EOL.$code)) {
                $this->info('Successfully added code to sidebar_content file.');
            } else {
                $this->error('Could not write to sidebar_content file.');
            }
        } else {
            $this->error('The sidebar_content file does not exist. Make sure Backpack is properly installed.');
        }
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
