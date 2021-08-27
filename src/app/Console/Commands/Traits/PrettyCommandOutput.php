<?php

namespace Backpack\CRUD\app\Console\Commands\Traits;

use Artisan;
use Illuminate\Console\Command;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

trait PrettyCommandOutput
{
    /**
     * Run a SSH command.
     *
     * @param string $command      The SSH command that needs to be run
     * @param bool   $beforeNotice Information for the user before the command is run
     * @param bool   $afterNotice  Information for the user after the command is run
     *
     * @return mixed Command-line output
     */
    public function executeProcess($command, $beforeNotice = false, $afterNotice = false)
    {
        $this->echo('info', $beforeNotice ? ' '.$beforeNotice : implode(' ', $command));

        // make sure the command is an array as per Symphony 4.3+ requirement
        $command = is_string($command) ? explode(' ', $command) : $command;

        $process = new Process($command, null, null, null, $this->option('timeout'));
        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                $this->echo('comment', $buffer);
            } else {
                $this->echo('line', $buffer);
            }
        });

        // executes after the command finishes
        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        if ($this->progressBar) {
            $this->progressBar->advance();
        }

        if ($afterNotice) {
            $this->echo('info', $afterNotice);
        }
    }

    /**
     * Run an artisan command.
     *
     * @param  string  $command      The artisan command to be run.
     * @param  array   $arguments    Key-value array of arguments to the artisan command.
     * @param  bool    $beforeNotice Information for the user before the command is run
     * @param  bool    $afterNotice  Information for the user after the command is run
     *
     * @return mixed Command-line output
     */
    public function executeArtisanProcess($command, $arguments = [], $beforeNotice = false, $afterNotice = false)
    {
        $beforeNotice = $beforeNotice ? ' '.$beforeNotice : 'php artisan '.implode(' ', (array) $command).' '.implode(' ', $arguments);

        $this->echo('info', $beforeNotice);

        try {
            Artisan::call($command, $arguments);
        } catch (Exception $e) {
            throw new ProcessFailedException($e);
        }

        if ($this->progressBar) {
            $this->progressBar->advance();
        }

        if ($afterNotice) {
            $this->echo('info', $afterNotice);
        }
    }

    /**
     * Write text to the screen for the user to see.
     *
     * @param string $type    line, info, comment, question, error
     * @param string $content
     */
    public function echo($type, $content)
    {
        if ($this->option('debug') == false) {
            return;
        }

        // skip empty lines
        if (trim($content)) {
            $this->{$type}($content);
        }
    }

    /**
     * Write a title inside a box.
     *
     * @param string $content
     */
    public function box($content)
    {
        for ($i = 0, $line = ''; $i < strlen($content); ++$i, $line .= '─');

        $this->line('');
        $this->info("┌───{$line}───┐");
        $this->info("│   $content   │");
        $this->info("└───{$line}───┘");
    }

    /**
     * Write a title inside a box.
     *
     * @param string $content
     */
    public function note($content)
    {
        $this->line("│ $content");
    }
}
