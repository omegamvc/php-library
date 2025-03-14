<?php

declare(strict_types=1);

namespace System\Console\Commands;

use System\Console\Command;
use System\Console\Style\ProgressBar;
use System\Console\Traits\PrintHelpTrait;
use System\Text\Str;
use System\View\Templator;

use function System\Console\info;
use function System\Console\ok;
use function System\Console\warn;

/**
 * @property string|null $prefix
 */
class ViewCommand extends Command
{
    use PrintHelpTrait;

    /**
     * Register command.
     *
     * @var array<int, array<string, mixed>>
     */
    public static array $command = [
        [
            'pattern' => 'view:cache',
            'fn'      => [ViewCommand::class, 'cache'],
            'default' => [
                'prefix' => '*.php',
            ],
        ], [
            'pattern' => 'view:clear',
            'fn'      => [ViewCommand::class, 'clear'],
            'default' => [
                'prefix' => '*.php',
            ],
        ],
    ];

    /**
     * @return array<string, array<string, string|string[]>>
     */
    public function printHelp(): array
    {
        return [
            'commands'  => [
                'view:cache' => 'Create all templator template (optimize)',
                'view:clear' => 'Clear all cached view file',
            ],
            'options'   => [
                '--prefix' => 'Finding file by pattern given',
            ],
            'relation'  => [
                'view:cache' => ['--prefix'],
                'view:clear' => ['--prefix'],
            ],
        ];
    }

    public function cache(Templator $templator): int
    {
        $files = glob(view_path() . $this->prefix);
        if (false === $files) {
            return 1;
        }
        info('build compiler cache')->out(false);
        $count     = 0;
        $progress = new ProgressBar(':progress :percent - :current', [
            ':current' => fn ($current, $max): string => array_key_exists($current, $files) ? Str::replace($files[$current], view_path(), '') : '',
        ]);

        $progress->maks = count($files);
        $watch_start     = microtime(true);
        foreach ($files as $file) {
            if (is_file($file)) {
                $filename = Str::replace($file, view_path(), '');
                $templator->compile($filename);
                $count++;
            }
            $progress->current++;
            $time                = round(microtime(true) - $watch_start, 3) * 1000;
            $progress->complete = static fn (): string => (string) ok("Success, {$count} file compiled ({$time} ms).");
            $progress->tick();
        }

        return 0;
    }

    public function clear(): int
    {
        warn('Clear cache file in ' . cache_path())->out(false);
        $files = glob(cache_path() . DIRECTORY_SEPARATOR . $this->prefix);

        if (false === $files || 0 === count($files)) {
            warn('No file cache clear.')->out();

            return 1;
        }

        $count     = 0;
        $progress = new ProgressBar(':progress :percent - :current', [
            ':current' => fn ($current, $max): string => array_key_exists($current, $files) ? Str::replace($files[$current], view_path(), '') : '',
        ]);

        $progress->maks = count($files);
        $watch_start     = microtime(true);
        foreach ($files as $file) {
            if (is_file($file)) {
                $count += unlink($file) ? 1 : 0;
            }
            $progress->current++;
            $time                = round(microtime(true) - $watch_start, 3) * 1000;
            $progress->complete = static fn (): string => (string) ok("Success, {$count} cache clear ({$time} ms).");
            $progress->tick();
        }

        return 0;
    }
}
