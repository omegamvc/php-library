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

/**
 * Class Schedule
 *
 * The Schedule class is responsible for managing a collection of scheduled tasks
 * (ScheduleTime) and executing them at the specified times. It allows adding,
 * retrieving, and executing scheduled tasks while supporting logging and retrying mechanisms.
 *
 * @category  System
 * @package   Cron
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version   2.0.0
 */
class Schedule
{
    /**
     * @var ScheduleTime[] $pools A collection of ScheduleTime objects representing the scheduled tasks.
     */
    protected array $pools = [];

    /**
     * Schedule constructor.
     *
     * @param int|null $time The time to set for the schedule (optional).
     * @param InterpolateInterface|null $logger The logger instance to handle log messages (optional).
     */
    public function __construct(
        protected ?int $time = null,
        private ?InterpolateInterface $logger = null
    ) {
    }

    /**
     * Retrieves the list of scheduled tasks.
     *
     * @return ScheduleTime[] An array of ScheduleTime objects.
     */
    public function getPools(): array
    {
        return $this->pools;
    }

    /**
     * Adds a new scheduled task to the pool with the provided callback and parameters.
     *
     * @param Closure $callback The callback function to execute for the scheduled task.
     * @param array $params The parameters to pass to the callback function.
     * @return ScheduleTime The created ScheduleTime object representing the scheduled task.
     */
    public function call(Closure $callback, array $params = []): ScheduleTime
    {
        return $this->pools[] = new ScheduleTime($callback, $params, $this->time);
    }

    /**
     * Executes all scheduled tasks in the pool, retrying if needed.
     *
     * @return void
     */
    public function execute(): void
    {
        foreach ($this->pools as $cron) {
            $cron->setLogger($this->logger);
            do {
                $cron->expect();
            } while ($cron->getAttempts() > 0);

            if ($cron->isAttempts()) {
                $cron->expect();
            }
        }
    }

    /**
     * Sets the logger instance for the schedule.
     *
     * @param InterpolateInterface $logger The logger instance.
     * @return void
     */
    public function setLogger(InterpolateInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Sets the time for the schedule.
     *
     * @param int $time The time to set for the schedule.
     * @return void
     */
    public function setTime(int $time): void
    {
        $this->time = $time;
    }

    /**
     * Adds the schedules from another Schedule instance to the current schedule's pool.
     *
     * @param Schedule $schedule The schedule to add to the current pool.
     * @return $this The current Schedule instance for method chaining.
     */
    public function add(Schedule $schedule): self
    {
        foreach ($schedule->getPools() as $time) {
            $this->pools[] = $time;
        }

        return $this;
    }

    /**
     * Clears all scheduled tasks in the pool.
     *
     * @return void
     */
    public function flush(): void
    {
        $this->pools = [];
    }
}
