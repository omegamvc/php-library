<?php

/**
 * Part of Omega - Console Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace System\Console\Commands;

use System\Console\Command;
use System\Console\Style\Style;
use System\Cron\InterpolateInterface;
use System\Cron\Schedule;
use System\Support\Facades\Schedule as Scheduler;
use System\Time\Now;

use function max;
use function microtime;
use function round;
use function sleep;
use function strlen;
use function System\Console\info;

/**
 * The CronCommand class handles the execution and management of scheduled tasks (cron jobs)
 * within the application. It provides functionality to run all scheduled jobs, list the available
 * schedules, and simulate cron execution in the terminal. Additionally, it offers help documentation
 * for the various cron-related commands.
 *
 * @category   System
 * @package    Console
 * @subpackage Commands
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
class CronCommand extends Command
{
    /** @var InterpolateInterface Log interpolator for cron output. */
    protected InterpolateInterface $log;

    /**
     * Command registration details.
     *
     * This array defines the commands available for managing the application's
     * maintenance mode. Each command is associated with a pattern and a function
     * that handles the command.
     *
     * @var array<int, array<string, mixed>>
     */
    public static array $command = [
        [
            'pattern' => 'cron',
            'fn'      => [self::class, 'main'],
        ], [
            'pattern' => 'cron:list',
            'fn'      => [self::class, 'list'],
        ], [
            'pattern' => 'cron:work',
            'fn'      => [self::class, 'work'],
        ],
    ];

    /**
     * Provides help documentation for the command.
     *
     * This method returns an array with information about available commands
     * and options. It describes the two main commands (`down` and `up`) for
     * managing maintenance mode.
     *
     * @return array<string, array<string, string|string[]>>
     */
    public function printHelp(): array
    {
        return [
            'commands'  => [
                'cron'      => 'Run cron job (all schedule)',
                'cron:work' => 'Run virtual cron job in terminal (async)',
                'cron:list' => 'Get list of schedule',
            ],
            'options'   => [],
            'relation'  => [],
        ];
    }

    /**
     * Executes all scheduled cron jobs.
     *
     * This method measures the execution time, runs the cron schedule, and outputs the total time taken.
     * It returns an exit code of 0 on success.
     *
     * @return int Exit status code (0 for success).
     */
    public function main(): int
    {
        $watchStart = microtime(true);

        $this->getSchedule()->execute();

        $watchEnd = round(microtime(true) - $watchStart, 3) * 1000;
        info('done in')
            ->push($watchEnd . 'ms')->textGreen()
            ->out();

        return 0;
    }

    /**
     * Lists the available cron schedules.
     *
     * This method collects all scheduled cron tasks, calculates the maximum string lengths for alignment,
     * and prints a formatted list of schedule times and event names along with execution status.
     *
     * @return int Exit status code (0 for success).
     */
    public function list(): int
    {
        $watchStart = microtime(true);
        $print      = new Style("\n");
        $info       = [];
        $max        = 0;

        foreach ($this->getSchedule()->getPools() as $cron) {
            $time   = $cron->getTimeName();
            $name   = $cron->getEventName();
            $info[] = [
                'time'   => $time,
                'name'   => $name,
                'animus' => $cron->isAnonymous(),
            ];
            $max = max(strlen($time), $max);
        }
        foreach ($info as $cron) {
            $print->push('#');
            if ($cron['animus']) {
                $print->push($cron['time'])->textDim()->repeat(' ', $max + 1 - strlen($cron['time']));
            } else {
                $print->push($cron['time'])->textGreen()->repeat(' ', $max + 1 - strlen($cron['time']));
            }
            $print->push($cron['name'])->textYellow()->newLines();
        }

        $watchEnd = round(microtime(true) - $watchStart, 3) * 1000;
        $print->newLines()->push('done in ')
            ->push($watchEnd . ' ms')->textGreen()
            ->out();

        return 0;
    }

    /**
     * Simulates a cron job execution in the terminal.
     *
     * This method continuously executes the cron schedule every 60 seconds while updating the terminal
     * with the execution time for each tick. It displays a message instructing the user to press Ctrl+C to stop.
     *
     * @return void
     */
    public function work(): void
    {
        $print = new Style("\n");
        $print
            ->push('Simulate Cron in terminal (every minute)')->textBlue()
            ->newLines(2)
            ->push('type ctrl+c to stop')->textGreen()->underline()
            ->out();

        $terminalWidth = $this->getWidth(34, 50);

        /* @phpstan-ignore-next-line */
        while (true) {
            $clock = new Now();
            $print = new Style();
            $time  = $clock->year . '-' . $clock->month . '-' . $clock->day;

            $print
                ->push('Run cron at - ' . $time)->textDim()
                ->push(' ' . $clock->hour . ':' . $clock->minute . ':' . $clock->second);

            $watchStart = microtime(true);

            $this->getSchedule()->execute();

            $watchEnd = round(microtime(true) - $watchStart, 3) * 1000;
            $print
                ->repeat(' ', $terminalWidth - $print->length())
                ->push('-> ')->textDim()
                ->push($watchEnd . 'ms')->textYellow()
                ->out()
            ;

            // reset every 60 seconds
            sleep(60);
        }
    }

    /**
     * Retrieves the current schedule of cron tasks.
     *
     * This method creates or updates a schedule using the Scheduler and applies any necessary
     * cron rules via the scheduler() method.
     *
     * @return Schedule The schedule containing all cron tasks.
     */
    protected function getSchedule(): Schedule
    {
        $schedule = Scheduler::add(new Schedule());
        $this->scheduler($schedule);

        return $schedule;
    }

    /**
     * Defines and configures the schedule for cron tasks.
     *
     * This method adds cron tasks to the given schedule, including setting retries,
     * execution conditions, and event names.
     *
     * @param Schedule $schedule The schedule to configure.
     * @return void
     */
    public function scheduler(Schedule $schedule): void
    {
        $schedule->call(fn () => [
            'code' => 200,
        ])
        ->setAttempts(2)
        ->justInTime()
        ->setAnonymousCronJob()
        ->setEventName('cli-schedule');

        // others schedule
    }
}
