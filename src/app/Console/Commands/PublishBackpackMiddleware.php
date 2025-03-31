<?php

namespace Backpack\CRUD\app\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class PublishBackpackMiddleware extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backpack:publish-middleware';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish the CheckIfAdmin middleware to App\Http\Middleware\CheckIfAdmin';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/../../Http/Middleware/CheckIfAdmin.php';
    }

    /**
     * Execute the console command.
     *
     * @return ?bool
     */
    public function handle()
    {
        $destination_path = $this->laravel['path'].'/Http/Middleware/CheckIfAdmin.php';

        if ($this->files->exists($destination_path)) {
            $this->error('CheckIfAdmin middleware already exists!');

            return false;
        }

        $this->makeDirectory($destination_path);

        $this->files->put($destination_path, $this->buildClass());

        $this->info($this->laravel->getNamespace().'Http\Middleware\CheckIfAdmin.php created successfully.');
    }

    /**
     * Build the class. Replace Backpack namespace with App one.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name = false)
    {
        $stub = $this->files->get($this->getStub());

        return $this->makeReplacements($stub);
    }

    /**
     * Replace the namespace for the given stub.
     * Replace the User model, if it was moved to App\Models\User.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return $this
     */
    protected function makeReplacements(&$stub)
    {
        $stub = str_replace('Backpack\CRUD\app\\', $this->laravel->getNamespace(), $stub);

        return $stub;
    }
}
