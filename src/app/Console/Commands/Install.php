<?php

namespace Backpack\CRUD\app\Console\Commands;

use Backpack\CRUD\BackpackServiceProvider;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class Install extends Command
{
    use Traits\PrettyCommandOutput;

    protected $progressBar;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backpack:install
                                {--timeout=300} : How many seconds to allow each process to run.
                                {--debug} : Show process output or not. Useful for debugging.
                                {--skip-basset-check : Skip running "php artisan basset:check" at the end of the installation.}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Backpack requirements on dev, publish files and create uploads directory.';

    /**
     * Addons variable.
     *
     * @var array
     */
    protected $addons = [
        Addons\RequirePro::class,
        Addons\RequireDevTools::class,
        Addons\RequireEditableColumns::class,
    ];

    /**
     * Themes variable.
     *
     * @var array
     */
    protected $themes = [
        Themes\RequireThemeTabler::class,
        Themes\RequireThemeCoreuiv4::class,
        Themes\RequireThemeCoreuiv2::class,
    ];

    /**
     * Execute the console command.
     *
     * @return mixed Command-line output
     */
    public function handle()
    {
        $this->infoBlock('Installing Backpack CRUD.', 'Step 1');

        // Publish files
        $this->progressBlock('Publishing configs, views, routes and assets');
        $this->executeArtisanProcess('vendor:publish', [
            '--provider' => BackpackServiceProvider::class,
            '--tag' => 'minimum',
        ]);
        $this->closeProgressBlock();

        // Create users table
        $this->progressBlock('Creating users table');
        try {
            if (! $this->option('no-interaction')) {
                \Illuminate\Support\Facades\DB::connection()->getPdo();
            }
        } catch (\Throwable $e) {
            $this->closeProgressBlock();

            $connection = config('database.default');
            $database = config("database.connections.{$connection}.database");

            if ($connection === 'sqlite' && ! file_exists($database)) {
                if ($this->confirm("The SQLite database file [$database] does not exist. Would you like to create it?", true)) {
                    touch($database);
                    $this->progressBlock('Database file created.');
                } else {
                    $this->errorBlock('Error accessing the database. Make sure your database is set up correctly and try again.');

                    return Command::FAILURE;
                }
            } else {
                $this->errorBlock('Error accessing the database. Make sure your database is set up correctly and try again.');

                return Command::FAILURE;
            }
        }

        $this->executeArtisanProcess('migrate', $this->option('no-interaction') ? ['--no-interaction' => true] : []);
        $this->closeProgressBlock();

        // Create CheckIfAdmin middleware
        $this->progressBlock('Creating CheckIfAdmin middleware');
        $this->executeArtisanProcess('backpack:publish-middleware');
        $this->closeProgressBlock();

        // Install Backpack Generators
        $this->progressBlock('Installing Generators');
        if (! file_exists('vendor/backpack/generators/composer.json')) {
            // only do this if Generators aren't already required
            $process = new Process(['composer', 'require', '--dev', 'backpack/generators']);
            $process->setTimeout(300);
            $process->run();
        }
        $this->closeProgressBlock();

        // Install Backpack Basset
        $this->progressBlock('Installing Basset');
        $this->executeArtisanProcess('basset:install --no-check --no-interaction');
        $this->closeProgressBlock();

        // Optional commands
        if (! $this->option('no-interaction')) {
            // Themes
            $this->installTheme();

            // Create users
            $this->createUsers();

            // Addons
            $this->installAddons();
        } elseif (! $this->isAnyThemeInstalled()) {
            // Install default theme
            $this->progressBlock('Installing default theme');
            $this->executeArtisanProcess('backpack:require:theme-tabler');
            $this->closeProgressBlock();
        }

        //execute basset checks
        if (! $this->option('skip-basset-check')) {
            $this->progressBlock('Running Basset checks');
            $this->closeProgressBlock('running', 'blue');

            $process = new Process([PHP_BINARY, 'artisan', 'basset:check']);
            $process->setTimeout(300);
            $process->run(function ($type, $buffer) {
                $this->output->write($buffer);
            });
        }
        // Done
        $url = Str::of(config('app.url'))->finish('/')->append('admin/');
        $this->infoBlock('Backpack installation complete.', 'done');
        $this->note("Go to <fg=blue>$url</> to access your new admin panel.");
        $this->note('You may need to run <fg=blue>php artisan serve</> to serve your Laravel project.');
        $this->newLine();
    }

    private function createUsers()
    {
        $userClass = config('backpack.base.user_model_fqn', 'App\Models\User');
        $userModel = new $userClass();

        $this->newLine();
        $this->infoBlock('Creating an admin.', 'Step 3');
        $this->note('Quickly jump in your admin panel, using the email & password you choose here.');

        // Test User Model DB Connection
        try {
            $userModel->getConnection()->getPdo();
        } catch (\Throwable $e) {
            $this->note('Error accessing the database, make sure the User Model has a valid DB connection.', 'red');

            return;
        }

        // Count current users
        $currentUsers = $userModel->count();
        $this->note(sprintf('Currently there %s in the <fg=blue>%s</> table.', trans_choice("{0} are <fg=blue>no users</>|{1} is <fg=blue>1 user</>|[2,*] are <fg=blue>$currentUsers users</>", $currentUsers), $userModel->getTable()));

        if ($currentUsers) {
            $this->note('Skipping creating an admin user.');
            $this->newLine();

            return;
        }

        $total = 0;
        while ($this->confirm(' Add '.($total ? 'another' : 'an').' admin user?')) {
            $name = $this->ask(' Name');
            $mail = $this->ask(" {$name}'s email");
            $pass = $this->secret(" {$name}'s password");

            try {
                $user = collect([
                    'name' => $name,
                    'email' => $mail,
                    'password' => Hash::make($pass),
                ]);

                // Merge timestamps
                if ($userModel->timestamps) {
                    $user = $user->merge([
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }

                $userModel->insert($user->toArray());

                $this->deleteLines(12);
                $this->progressBlock('Adding admin user');
                $this->closeProgressBlock();
                $this->note($name);
                $this->note($mail);

                $total++;
            } catch (\Throwable$e) {
                $this->errorBlock($e->getMessage());
            }
        }

        $this->deleteLines(3);

        if (! $total) {
            $this->deleteLines();
            $this->note('Skipping creating an admin user.');
            $this->newLine();
        }
    }

    private function isEveryAddonInstalled()
    {
        return collect($this->addons)->every(function ($addon) {
            return file_exists($addon->path);
        });
    }

    private function updateAddonsStatus()
    {
        $this->addons = $this->addons->each(function (&$addon) {
            $isInstalled = file_exists($addon->path);
            $addon->status = $isInstalled ? 'installed' : 'not installed';
            $addon->statusColor = $isInstalled ? 'green' : 'yellow';
        });
    }

    private function installAddons()
    {
        // map the addons
        $this->addons = collect($this->addons)
            ->map(function ($class) {
                return (object) $class::$addon;
            });

        // set addons current status (installed / not installed)
        $this->updateAddonsStatus();

        // if all addons are installed do nothing
        if ($this->isEveryAddonInstalled()) {
            return;
        }

        $this->infoBlock('Installing premium Backpack add-ons:', 'Step 4');
        $this->note('Add tons of features and functionality to your admin panel, using our paid add-ons.');
        $this->note('For more information, payment and access please visit <fg=blue>https://backpackforlaravel.com/pricing</>');
        $this->newLine();

        // Calculate the printed line count
        $printedLines = $this->addons
            ->map(function ($e) {
                return count($e->description);
            })
            ->reduce(function ($sum, $item) {
                return $sum + $item + 2;
            }, 0);

        $total = 0;
        while (! $this->isEveryAddonInstalled()) {
            $input = (int) $this->listChoice('Would you like to install a premium Backpack add-on? <fg=gray>(enter an option number from above or press any other key to continue the installation)</>', $this->addons->toArray());

            if ($input < 1 || $input > $this->addons->count()) {
                $this->deleteLines(3);
                break;
            }

            // Clear list
            $this->deleteLines($printedLines + 4 + ($total ? 2 : 0));

            try {
                $addon = $this->addons[$input - 1];

                // Install addon (low verbose level)
                $currentVerbosity = $this->output->getVerbosity();
                $this->output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
                $this->call($addon->command);
                $this->output->setVerbosity($currentVerbosity);

                // refresh list
                $this->updateAddonsStatus();

                $total++;
            } catch (\Throwable $e) {
                $this->errorBlock($e->getMessage());
            }

            $this->line('  ──────────', 'fg=gray');
            $this->newLine();
        }
    }

    private function isEveryThemeInstalled()
    {
        return $this->themes()->every(function ($theme) {
            return $theme->status == 'installed';
        });
    }

    private function isAnyThemeInstalled()
    {
        return $this->themes()->filter(function ($theme) {
            return $theme->status == 'installed';
        })->count() > 0;
    }

    private function installTheme()
    {
        // if all themes are installed do nothing
        if ($this->isEveryThemeInstalled()) {
            return;
        }

        $this->infoBlock('Installing a Theme.', 'Step 2');
        $this->note('Choose your admin UI, depending on your project and preferences.');
        $this->newLine();

        // Calculate the printed line count
        $printedLines = $this->themes()
            ->map(function ($e) {
                return count($e->description);
            })
            ->reduce(function ($sum, $item) {
                return $sum + $item + 2;
            }, 0);

        $total = 0;
        $input = (int) $this->listChoice('Which Backpack theme would you like to install? <fg=gray>(enter option number: 1, 2 or 3)</>', $this->themes()->toArray(), 1);

        if ($input < 1 || $input > $this->themes()->count()) {
            $this->deleteLines(3);
            $this->note('Unknown theme. Using default theme value.');
            $this->newLine();

            $input = 1;
        }

        // Clear list
        $this->deleteLines($printedLines + 4 + ($total ? 2 : 0));

        try {
            $addon = $this->themes()[$input - 1];

            // Install addon (low verbose level)
            $currentVerbosity = $this->output->getVerbosity();
            $this->output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
            $this->call($addon->command);
            $this->output->setVerbosity($currentVerbosity);

            $total++;
        } catch (\Throwable $e) {
            $this->errorBlock($e->getMessage());
        }
    }

    public function themes()
    {
        return collect($this->themes)
            ->map(function ($class) {
                return (object) $class::$addon;
            })->each(function (&$theme) {
                $isInstalled = file_exists($theme->path);
                $theme->status = $isInstalled ? 'installed' : 'not installed';
                $theme->statusColor = $isInstalled ? 'green' : 'yellow';
            });
    }
}
