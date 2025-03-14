<?php

declare(strict_types=1);

namespace System\Console;

use System\Application\Application;
use System\Config\ConfigRepository;
use System\Integrate\ValueObjects\CommandMap;

final class Util
{
    /**
     * Convert command from array to CommandMap.
     *
     * @return CommandMap[]
     */
    public static function loadCommandFromConfig(Application $app): array
    {
        $command_map = [];
        foreach ($app[ConfigRepository::class]->get('commands', []) as $commands) {
            foreach ($commands as $command) {
                $command_map[] = new CommandMap($command);
            }
        }

        return $command_map;
    }
}
