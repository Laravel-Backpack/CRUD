<?php

namespace Backpack\CRUD\app\Console\Commands\Traits;

use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

trait AddonsHelper
{
    /**
     * Adds backpack info to repositories object in composer.json.
     *
     * @return void
     */
    public function composerRepositories()
    {
        $backpackRepo = 'https://repo.backpackforlaravel.com/';

        $details = null;
        $process = new Process(['composer', 'config', 'repositories']);
        $process->run(function ($type, $buffer) use (&$details) {
            if ($type !== Process::ERR && $buffer !== '') {
                $details = json_decode($buffer);
            } else {
                $details = json_decode(File::get('composer.json'))->repositories ?? false;
            }
        });

        // validate if backpack repo exists
        if (collect($details)->pluck('url')->contains($backpackRepo)) {
            $this->note('Backpack repository entry is present in composer.json.');

            return;
        }

        // Create repositories
        $this->progressBlock('Creating repositories entry in composer.json');

        $process = new Process(['composer', 'config', 'repositories.backpack', 'composer', $backpackRepo]);
        $process->run(function ($type, $buffer) use ($backpackRepo) {
            if ($type === Process::ERR) {
                // Fallback
                $composerJson = Str::of(File::get('composer.json'));

                $currentRepositories = json_decode($composerJson)->repositories ?? false;

                $repositories = Str::of(json_encode([
                    'repositories' => [
                        'backpack' => [
                            'type' => 'composer',
                            'url' => $backpackRepo,
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
        });

        $this->closeProgressBlock();
    }

    /**
     * Checks for backpack authentication.
     *
     * @return void
     */
    public function checkForAuthentication()
    {
        // Check if auth exists
        $details = null;
        $process = new Process(['composer', 'config', 'http-basic.backpackforlaravel.com']);
        $process->run(function ($type, $buffer) use (&$details) {
            if ($type !== Process::ERR && $buffer !== '') {
                $details = json_decode($buffer);
            } elseif (File::exists('auth.json')) {
                $details = json_decode(File::get('auth.json'), true)['http-basic']['backpackforlaravel.com'] ?? false;
            }
        });

        // Token exists
        if ($details) {
            $this->note('Backpack authentication token is present.');

            return;
        }

        // Create an auth.json file
        $this->progressBlock('Creating auth.json file with your authentication token');
        $this->newLine();

        $this->note('(Find your access token details on https://backpackforlaravel.com/user/tokens)');
        $username = $this->ask('Access token username');
        $password = $this->ask('Access token password');

        $this->deleteLines(9);
        $this->progressBlock('Creating auth.json file with your authentication token');

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
        });

        $this->closeProgressBlock();
    }

    /**
     * Clears authentication from auth.json file.
     *
     * @return void
     */
    public function clearAuthentication()
    {
        if (File::exists('auth.json')) {
            $auth = json_decode(File::get('auth.json'));
            if ($auth->{'http-basic'}->{'backpackforlaravel.com'} ?? false) {
                unset($auth->{'http-basic'}->{'backpackforlaravel.com'});

                File::put('auth.json', json_encode($auth, JSON_PRETTY_PRINT));
            }
        }
    }

    /**
     * Composer require helper.
     *
     * @return void
     */
    public function composerRequire(string $package, array $options = [])
    {
        // Require package
        $process = new Process(array_merge(['composer', 'require'], $options, [$package]));
        $process->setTimeout(300);
        $process->run(function ($type, $buffer) use ($process) {
            if ($type === Process::ERR) {
                Log::error($buffer);
            }

            if (strpos($buffer, 'Permission denied') !== false) {
                // Clear authentication
                $this->clearAuthentication();

                throw new Exception('Permission denied. Could not authenticate the credentials.');
            }

            if (strpos($buffer, 'curl error') !== false) {
                $process->stop(0);

                throw new Exception('Connection refused. Failed to connect due to network issues.');
            }

            if (strpos($buffer, 'Your requirements could not be resolved') !== false) {
                $process->stop(0);

                throw new Exception($buffer);
            }
        });
    }
}
