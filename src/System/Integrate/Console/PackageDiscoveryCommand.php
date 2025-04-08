<?php

declare(strict_types=1);

namespace System\Integrate\Console;

use System\Console\Command;
use System\Console\Style\Style;
use System\Application\Application;
use System\Support\PackageManifest;
use Throwable;

use function array_keys;
use function System\Console\fail;
use function System\Console\info;

class PackageDiscoveryCommand extends Command
{
    /**
     * Register command.
     *
     * @var array<int, array<string, mixed>>
     */
    public static array $command = [
        [
            'pattern' => 'package:discovery',
            'fn'      => [self::class, 'discovery'],
        ],
    ];

    /**
     * @return array<string, array<string, string|string[]>>
     */
    public function printHelp(): array
    {
        return [
            'commands'  => [
                'package:discovery' => 'Discovery package in composer',
            ],
            'options'   => [],
            'relation'  => [],
        ];
    }

    public function discovery(Application $app): int
    {
        $package = $app[PackageManifest::class];
        info('Trying build package cache.')->out(false);
        try {
            $package->build();

            $packages = (fn () => $this->{'getPackageManifest'}())->call($package) ?? [];
            $style    = new Style();
            foreach (array_keys($packages) as $name) {
                $length = $this->getWidth(40, 60) - strlen($name) - 4;
                $style->push($name)->repeat('.', $length)->textDim()->push('DONE')->textGreen()->newLines();
            }
            $style->out(false);
        } catch (Throwable $th) {
            fail($th->getMessage())->out(false);
            fail('Can\'t create package manifest cache file.')->out();

            return 1;
        }

        return 0;
    }
}
