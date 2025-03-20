<?php

/**
 * Part of Omega - Cron Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace System\Cron;

use Closure;
use Throwable;

use function call_user_func;
use function date;
use function microtime;
use function range;
use function round;

/**
 * Manages scheduled execution times for cron jobs.
 *
 * This class provides functionality to define, manage, and evaluate scheduled
 * execution times for cron jobs. It allows setting up various scheduling
 * patterns, handling retry logic, and determining whether a task should run at
 * a given moment.
 *
 * @category  System
 * @package   Cron
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version   2.0.0
 */
class ScheduleTime extends AbstractScheduleTime
{
    /**
     * Initializes the schedule time instance.
     *
     * @param Closure $callback The callback function to execute when the scheduled time is due.
     * @param array   $params   Parameters to pass to the callback function.
     * @param int     $time     The current timestamp.
     * @return void
     */
    public function __construct(Closure $callback, array $params, int $time)
    {
        parent::__construct($callback, $params, $time);
    }

    /**
     * {@inheritdoc}
     */
    public function isDue(): bool
    {
        $events = $this->timeExpect;

        $dayLetter  = date('D', $this->time);
        $day        = date('d', $this->time);
        $hour       = date('H', $this->time);
        $minute     = date('i', $this->time);

        foreach ($events as $event) {
            $eventDayLetter = $event['D'] ?? $dayLetter; // default day letter every event

            if (
                $eventDayLetter == $dayLetter
                && $event['d'] == $day
                && $event['h'] == $hour
                && $event['m'] == $minute
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function retryIf(bool $condition): self
    {
        $this->retryCondition = $condition;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function skip(bool|Closure $skipWhen): self
    {
        if ($skipWhen instanceof Closure) {
            $skipWhen = $skipWhen();
        }

        $this->skip = $skipWhen;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @throws Throwable
     */
    public function expect(): void
    {
        if ($this->isDue() && false === $this->skip) {
            // stopwatch
            $watchStart = microtime(true);

            try {
                $out_put             = call_user_func($this->callback, $this->params) ?? [];
                $this->retryAttempts = 0;
                $this->isFail       = false;
            } catch (Throwable $th) {
                $this->retryAttempts--;
                $this->isFail = true;
                $out_put       = [
                    'error' => $th->getMessage(),
                ];
            }

            // stopwatch
            $watchEnd = round(microtime(true) - $watchStart, 3) * 1000;

            // send command log
            if (!$this->anonymous) {
                $this->logger?->interpolate(
                    $this->eventName,
                    [
                        'execute_time'  => $watchEnd,
                        'cron_time'     => $this->time,
                        'event_name'    => $this->eventName,
                        'attempts'      => $this->retryAttempts,
                        'error_message' => $out_put,
                    ]
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function interpolate(string $message, array $context): void
    {
        // do stuff
    }

    /**
     * {@inheritdoc}
     */
    public function justInTime(): self
    {
        $this->timeName   = __FUNCTION__;
        $this->timeExpect = [
            [
                'D' => date('D', $this->time),
                'd' => date('d', $this->time),
                'h' => date('H', $this->time),
                'm' => date('i', $this->time),
            ],
        ];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function everyTenMinute(): self
    {
        $this->timeName  = __FUNCTION__;
        $minute          = [];
        foreach (range(0, 59) as $time) {
            if ($time % 10 == 0) {
                $minute[] = [
                    'd' => date('d', $this->time),
                    'h' => date('H', $this->time),
                    'm' => $time,
                ];
            }
        }

        $this->timeExpect = $minute;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function everyThirtyMinutes(): self
    {
        $this->timeName   = __FUNCTION__;
        $this->timeExpect = [
            [
                'd' => date('d', $this->time),
                'h' => date('H', $this->time),
                'm' => 0,
            ],
            [
                'd' => date('d', $this->time),
                'h' => date('H', $this->time),
                'm' => 30,
            ],
        ];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function everyTwoHour(): self
    {
        $this->timeName = __FUNCTION__;

        $thisDay = date('d', $this->time);
        $hourly  = []; // from 00.00 to 23.00 (12 time)
        foreach (range(0, 23) as $time) {
            if ($time % 2 == 0) {
                $hourly[] = [
                    'd' => $thisDay,
                    'h' => $time,
                    'm' => 0,
                ];
            }
        }

        $this->timeExpect = $hourly;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function everyTwelveHour(): self
    {
        $this->timeName   = __FUNCTION__;
        $this->timeExpect = [
            [
                'd' => date('d', $this->time),
                'h' => 0,
                'm' => 0,
            ],
            [
                'd' => date('d', $this->time),
                'h' => 12,
                'm' => 0,
            ],
        ];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hourly(): self
    {
        $this->timeName  = __FUNCTION__;
        $hourly          = []; // from 00.00 to 23.00 (24 time)

        foreach (range(0, 23) as $time) {
            $hourly[] = [
                'd' => date('d', $this->time),
                'h' => $time,
                'm' => 0,
            ];
        }

        $this->timeExpect = $hourly;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hourlyAt(int $hour24): self
    {
        $this->timeName   = __FUNCTION__;
        $this->timeExpect = [
            [
                'd' => date('d', $this->time),
                'h' => $hour24,
                'm' => 0,
            ],
        ];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function daily(): self
    {
        $this->timeName   = __FUNCTION__;
        $this->timeExpect = [
            // from day 1 to 31 (31 time)
            ['d' => (int) date('d'), 'h' => 0, 'm' => 0],
        ];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function dailyAt(int $day): self
    {
        $this->timeName   = __FUNCTION__;
        $this->timeExpect = [
            [
                'd' => $day,
                'h' => 0,
                'm' => 0,
            ],
        ];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function weekly(): self
    {
        $this->timeName   = __FUNCTION__;
        $this->timeExpect = [
            [
                'D' => 'Sun',
                'd' => date('d', $this->time),
                'h' => 0,
                'm' => 0,
            ],
        ];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function monthly(): self
    {
        $this->timeName   = __FUNCTION__;
        $this->timeExpect = [
            [
                'd' => 1,
                'h' => 0,
                'm' => 0,
            ],
        ];

        return $this;
    }
}
