<?php

namespace Backpack\CRUD\app\Console\Commands\Traits;

use Artisan;
use Illuminate\Console\Command;
use Symfony\Component\Console\Terminal;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

trait PrettyCommandOutput
{
    /**
     * Run a SSH command.
     *
     * @param  string  $command  The SSH command that needs to be run
     * @param  bool  $beforeNotice  Information for the user before the command is run
     * @param  bool  $afterNotice  Information for the user after the command is run
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

        if ($this->progressBar ?? null) {
            $this->progressBar->advance();
        }

        if ($afterNotice) {
            $this->echo('info', $afterNotice);
        }
    }

    /**
     * Run an artisan command.
     *
     * @param  string  $command  the artisan command to be run
     * @param  array  $arguments  key-value array of arguments to the artisan command
     * @param  bool  $beforeNotice  Information for the user before the command is run
     * @param  bool  $afterNotice  Information for the user after the command is run
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

        if ($this->progressBar ?? null) {
            $this->progressBar->advance();
        }

        if ($afterNotice) {
            $this->echo('info', $afterNotice);
        }
    }

    /**
     * Write text to the screen for the user to see.
     *
     * @param  string  $type  line, info, comment, question, error
     * @param  string  $content
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
     * @param  string  $header
     */
    public function box($header, $color = 'green')
    {
        $line = str_repeat('─', strlen($header));

        $this->newLine();
        $this->line("<fg=$color>┌───{$line}───┐</>");
        $this->line("<fg=$color>│   $header   │</>");
        $this->line("<fg=$color>└───{$line}───┘</>");
    }

    /**
     * List choice element.
     *
     * @return void
     */
    public function listChoice(string $question, array $options, string $default = 'no', ?string $hint = null)
    {
        foreach ($options as $key => $option) {
            $value = $key + 1;
            $this->progressBlock("<fg=yellow>$value</> {$option->name}");
            $this->closeProgressBlock($option->status, $option->statusColor ?? '');
            foreach ($option->description ?? [] as $line) {
                $this->line("    <fg=gray>{$line}</>");
            }
            $this->newLine();
        }

        return $this->ask(" $question", $default);
    }

    /**
     * Default info block element.
     *
     * @return void
     */
    public function infoBlock(string $text, string $title = 'info', string $background = 'blue', string $foreground = 'white')
    {
        $this->newLine();

        // low verbose level (-v) will display a note instead of info block
        if ($this->output->isVerbose()) {
            if ($title !== 'info') {
                $text = "$text <fg=gray>[<fg=$background>$title</>]</>";
            }

            return $this->line("  $text");
        }

        $this->line(sprintf("  <fg=$foreground;bg=$background> %s </> $text", strtoupper($title)));
        $this->newLine();
    }

    /**
     * Default error block element
     * Shortcute to info block with error message.
     *
     * @return void
     */
    public function errorBlock(string $text)
    {
        $this->infoBlock($text, 'ERROR', 'red');
    }

    /**
     * Note element, usually used after an info block
     * Prints an indented text with a lighter color.
     *
     * @return void
     */
    public function note(string $text, string $color = 'gray', string $barColor = 'gray')
    {
        $this->line("  <fg=$barColor>│</> $text", "fg=$color");
    }

    /**
     * Progress element generates a pending in progress line block.
     *
     * @return void
     */
    public function progressBlock(string $text, string $progress = 'running', string $color = 'blue')
    {
        $this->maxWidth = $this->maxWidth ?? 128;
        $this->terminal = $this->terminal ?? new Terminal();
        $width = min($this->terminal->getWidth(), $this->maxWidth);
        $dotLength = $width - 5 - strlen(strip_tags($text.$progress));

        // In case it doesn't fit the screen, add enough lines with dots
        $textLength = strlen(strip_tags($text)) + 20;
        $dotLength += floor($textLength / $width) * $width;

        $this->consoleProgress = $progress;

        $this->output->write(sprintf(
            "  $text <fg=gray>%s</> <fg=$color>%s</>",
            str_repeat('.', max(1, $dotLength)),
            strtoupper($progress)
        ));
    }

    /**
     * Closes a progress block after it has been started.
     *
     * @return void
     */
    public function closeProgressBlock(string $progress = 'done', string $color = 'green')
    {
        $deleteSize = max(strlen($this->consoleProgress ?? ''), strlen($progress)) + 1;
        $newDotSize = $deleteSize - strlen($progress) - 1;

        $this->deleteChars($deleteSize);

        $this->output->write(sprintf(
            "<fg=gray>%s</> <fg=$color>%s</>",
            $newDotSize > 0 ? str_repeat('.', $newDotSize) : '',
            strtoupper($progress),
        ));
        $this->newLine();
    }

    /**
     * Closes a progress block with an error.
     *
     * @return void
     */
    public function errorProgressBlock(string $text = 'error')
    {
        $this->closeProgressBlock($text, 'red');
    }

    /**
     * Deletes one or multiple lines.
     *
     * @return void
     */
    public function deleteLines(int $amount = 1)
    {
        $this->output->write(str_repeat("\033[A\33[2K\r", $amount));
    }

    /**
     * @return void
     */
    public function askHint(string $question, array $hints, string $default)
    {
        $hints = collect($hints)
            ->map(function ($hint) {
                return " <fg=gray>│ $hint</>";
            })
            ->join(PHP_EOL);

        return $this->ask($question.PHP_EOL.$hints, $default);
    }

    /**
     * Deletes one or multiple chars.
     *
     * @return void
     */
    public function deleteChars(int $amount = 1)
    {
        $this->output->write(str_repeat(chr(8), $amount));
    }
}
