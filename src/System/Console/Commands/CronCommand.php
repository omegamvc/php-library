<?php

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
use function strlen;
use function System\Console\info;

class CronCommand extends Command
{
    protected InterpolateInterface $log;

    /**
     * Register command.
     *
     * @var array<int, array<string, mixed>>
     */
    public static array $command = [
        [
            'pattern' => 'cron',
            'fn'      => [CronCommand::class, 'main'],
        ], [
            'pattern' => 'cron:list',
            'fn'      => [CronCommand::class, 'list'],
        ], [
            'pattern' => 'cron:work',
            'fn'      => [CronCommand::class, 'work'],
        ],
    ];

    /**
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

    public function list(): int
    {
        $watchStart = microtime(true);
        $print      = new Style("\n");

        $info = [];
        $max  = 0;
        foreach ($this->getSchedule()->getPools() as $cron) {
            $time   = $cron->getTimeName();
            $name   = $cron->getEventname();
            $info[] = [
                'time'   => $time,
                'name'   => $name,
                'animus' => $cron->isAnimusly(),
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
            // E' replicato con il comando in app/Commands che è solo
            // un esempio di comando personalizzato
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

    protected function getSchedule(): Schedule
    {
        $schedule = Scheduler::add(new Schedule());
        $this->scheduler($schedule);

        return $schedule;
    }

    public function scheduler(Schedule $schedule): void
    {
        $schedule->call(fn () => [
            'code' => 200,
        ])
        ->retry(2)
        ->justInTime()
        ->animusly()
        ->eventName('cli-schedule');

        // others schedule
    }
}
