<?php

declare(strict_types=1);

namespace System\Console\Commands;

use System\Console\Command;
use System\Console\Style\ProgressBar;
use System\Console\Traits\PrintHelpTrait;
use System\Text\Str;
use System\View\Templator;

use function array_key_exists;
use function count;
use function get_resources_path;
use function get_storage_path;
use function glob;
use function is_file;
use function microtime;
use function round;
use function System\Console\info;
use function System\Console\ok;
use function System\Console\warn;
use function unlink;

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
            'fn'         => [ViewCommand::class, 'cache'],
            'default' => [
                'prefix' => '*.php',
            ],
        ], [
            'pattern' => 'view:clear',
            'fn'         => [ViewCommand::class, 'clear'],
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
                '--prefix'   => 'Finding file by pattern given',
            ],
            'relation'  => [
                'view:cache' => ['--prefix'],
                'view:clear' => ['--prefix'],
            ],
        ];
    }

    /**
     * @param Templator $templator
     * @return int
     */
    public function cache(Templator $templator): int
    {
        $files = glob(get_resources_path('views/') . $this->prefix);
        if (false === $files) {
            return 1;
        }

        info('build compiler cache')->out(false);

        $count    = 0;
        $progress = new ProgressBar(':progress :percent - :current', [
            ':current' => fn ($current, $max): string => array_key_exists($current, $files) ? Str::replace($files[$current], get_resources_path('views/'), '') : '',
        ]);

        $progress->maks = count($files);
        $watchStart     = microtime(true);

        foreach ($files as $file) {
            if (is_file($file)) {
                $filename = Str::replace($file, get_resources_path('views/'), '');
                $templator->compile($filename);
                $count++;
            }

            $progress->current++;
            $time                = round(microtime(true) - $watchStart, 3) * 1000;
            $progress->complete = static fn (): string => (string) ok("Success, {$count} file compiled ({$time} ms).");
            $progress->tick();
        }

        return 0;
    }

    /**
     * @return int
     */
    public function clear(): int
    {
        warn('Clear cache file in ' . get_storage_path('app/view/'))->out(false);
        $files = glob(get_storage_path('app/view/') . $this->prefix);

        if (false === $files || 0 === count($files)) {
            warn('No file cache clear.')->out();

            return 1;
        }

        $count    = 0;
        $progress = new ProgressBar(':progress :percent - :current', [
            ':current' => fn ($current, $max): string => array_key_exists($current, $files) ? Str::replace($files[$current], get_resources_path('views/'), '') : '',
        ]);

        $progress->maks = count($files);
        $watchStart     = microtime(true);
        foreach ($files as $file) {
            if (is_file($file)) {
                $count += unlink($file) ? 1 : 0;
            }
            $progress->current++;
            $time                = round(microtime(true) - $watchStart, 3) * 1000;
            $progress->complete = static fn (): string => (string) ok("Success, {$count} cache clear ({$time} ms).");
            $progress->tick();
        }

        return 0;
    }
}
