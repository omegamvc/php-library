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
 * Interface for defining schedule time related methods.
 *
 * This interface enforces the implementation of methods for handling various scheduling functionalities
 * such as setting event names, checking the schedule, retrying, skipping execution, and logging messages.
 *
 * @category  System
 * @package   Cron
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version   2.0.0
 */
interface ScheduleTimeInterface
{
    /**
     * Sets the event name.
     *
     * @param string $val The name of the event.
     * @return $this
     */
    public function setEventName(string $val): self;

    /**
     * Gets the event name.
     *
     * @return string The name of the event.
     */
    public function getEventName(): string;

    /**
     * Marks the cron job as anonymous.
     *
     * @param bool $runAsAnonymous Set to true to run the job anonymously.
     * @return $this
     */
    public function setAnonymousCronJob(bool $runAsAnonymous = true): self;

    /**
     * Checks if the cron job is anonymous.
     *
     * @return bool True if the job is anonymous, otherwise false.
     */
    public function isAnonymous(): bool;

    /**
     * Gets the cron time name.
     *
     * @return string The name associated with the cron time.
     */
    public function getTimeName(): string;

    /**
     * Gets the expected execution times for the cron job.
     *
     * @return array<int, array<string, int|string>|int> An array representing the cron schedule.
     */
    public function getTimeExpect(): array;

    /**
     * Checks if the last cron execution failed.
     *
     * @return bool True if the execution failed, otherwise false.
     */
    public function isFail(): bool;

    /**
     * Gets the number of retry attempts.
     *
     * @return int The number of retries allowed.
     */
    public function getAttempts(): int;

    /**
     * Sets the number of retry attempts.
     *
     * @param int $attempt The number of retry attempts.
     * @return $this
     */
    public function setAttempts(int $attempt): self;

    /**
     * Checks if retrying attempts is enabled.
     *
     * @return bool True if retrying is enabled, otherwise false.
     */
    public function isAttempts(): bool;

    /**
     * Sets the logger instance.
     *
     * @param InterpolateInterface $logger The logger instance.
     * @return void
     */
    public function setLogger(InterpolateInterface $logger): void;

    /**
     * Checks if the current time matches the scheduled execution time.
     *
     * @return bool True if the job is due, otherwise false.
     */
    public function isDue(): bool;

    /**
     * Enables or disables retry based on a condition.
     *
     * @param bool $condition True to enable retrying, false to disable it.
     * @return $this
     */
    public function retryIf(bool $condition): self;

    /**
     * Skips the scheduled execution if the condition is met.
     *
     * @param bool|Closure(): bool $skipWhen A boolean or a closure that determines whether to skip the execution.
     * @return ScheduleTime
     */
    public function skip(bool|Closure $skipWhen): self;

    /**
     * Executes the scheduled task if it is due and not skipped.
     *
     * @return void
     */
    public function expect(): void;

    /**
     * Interpolates log messages with context values.
     *
     * @param string $message The log message template.
     * @param array<string, mixed> $context The context values to replace placeholders in the message.
     * @return void
     */
    public function interpolate(string $message, array $context): void;

    /**
     * Schedules the job to run immediately.
     *
     * @return $this
     */
    public function justInTime(): self;

    /**
     * Schedules the job to run every ten minutes.
     *
     * @return $this
     */
    public function everyTenMinute(): self;

    /**
     * Schedules the job to run every thirty minutes.
     *
     * @return $this
     */
    public function everyThirtyMinutes(): self;

    /**
     * Schedules the job to run every two hours.
     *
     * @return $this
     */
    public function everyTwoHour(): self;

    /**
     * Schedules the job to run every twelve hours.
     *
     * @return $this
     */
    public function everyTwelveHour(): self;

    /**
     * Schedules the job to run every hour.
     *
     * @return $this
     */
    public function hourly(): self;

    /**
     * Schedules the job to run at a specific hour.
     *
     * @param int $hour24 The hour (0-23) at which to execute the job.
     * @return $this
     */
    public function hourlyAt(int $hour24): self;

    /**
     * Schedules the job to run once per day at midnight.
     *
     * @return $this
     */
    public function daily(): self;

    /**
     * Schedules the job to run on a specific day of the month.
     *
     * @param int $day The day of the month (1-31).
     * @return $this
     */
    public function dailyAt(int $day): self;

    /**
     * Schedules the job to run once per week on Sunday.
     *
     * @return $this
     */
    public function weekly(): self;

    /**
     * Schedules the job to run once per month on the 1st day.
     *
     * @return $this
     */
    public function monthly(): self;
}
