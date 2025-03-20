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
 * Abstract class for handling schedule time functionalities.
 *
 * This abstract class provides the foundational properties and methods for scheduling tasks. It implements
 * the `ScheduleTimeInterface` and defines common functionality for managing event names, retry attempts, logging,
 * and handling cron job behaviors. Concrete implementations must define the specific logic for scheduling,
 * execution conditions, and the task-specific behavior of the cron jobs.
 *
 * @category  System
 * @package   Cron
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version   2.0.0
 */
abstract class AbstractScheduleTime implements ScheduleTimeInterface
{
    /** @var Closure The callback function to execute when the scheduled time is due. */
    protected Closure $callback;

    /** @var array Parameters to pass to the callback function. */
    protected array $params = [];

    /** @var int The current timestamp. */
    protected int $time;

    /** @var string Event name. */
    protected string $eventName = 'anonymous';

    /** @var array<int, array<string, int|string>|int> Times to check (cron time). */
    protected array $timeExpect;

    /** @var string Cron time name. */
    protected string $timeName = '';

    /** @var bool Determines if the cron job is anonymous. */
    protected bool $anonymous  = false;

    /** @var bool Indicates whether the cron execution encountered an error. */
    protected bool $isFail = false;

    /** @var int Defines the maximum number of retry attempts. */
    protected int $retryAttempts = 0;

    /** @var bool Determines whether a retry should be attempted based on a condition. */
    protected bool $retryCondition = false;

    /** @var bool Determines whether the task should be skipped under certain conditions. */
    protected bool $skip = false;

    /** @var InterpolateInterface|null Logger for message interpolation. */
    protected ?InterpolateInterface $logger = null;

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
        $this->callback = $callback;
        $this->params   = $params;
        $this->time     = $time;
        $this->timeExpect = [
            [
                'D' => date('D', $this->time),
                'd' => date('d', $this->time),
                'h' => date('H', $this->time),
                'm' => date('i', $this->time),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setEventName(string $val): self
    {
        $this->eventName = $val;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getEventName(): string
    {
        return $this->eventName;
    }

    /**
     * {@inheritdoc}
     */
    public function setAnonymousCronJob(bool $runAsAnonymous = true): self
    {
        $this->anonymous = $runAsAnonymous;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isAnonymous(): bool
    {
        return $this->anonymous;
    }

    /**
     * {@inheritdoc}
     */
    public function getTimeName(): string
    {
        return $this->timeName;
    }

    /**
     * {@inheritdoc}
     */
    public function getTimeExpect(): array
    {
        return $this->timeExpect;
    }

    /**
     * {@inheritdoc}
     */
    public function isFail(): bool
    {
        return $this->isFail;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttempts(): int
    {
        return $this->retryAttempts;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttempts(int $attempt): self
    {
        $this->retryAttempts = $attempt;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isAttempts(): bool
    {
        return $this->retryCondition;
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(InterpolateInterface $logger): void
    {
        $this->logger = $logger;
    }
}
