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

namespace System\Console\Style\Color;

use System\Console\Style\RuleInterface;

use function implode;

/**
 * AbstractColor class.
 *
 * Base class for defining terminal color rules.
 *
 * This abstract class implements `RuleInterface` and provides a common
 * structure for handling ANSI color rules used for text formatting.
 *
 * @category   System
 * @package    Console
 * @subpackage Style\Color
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
abstract class AbstractColor implements RuleInterface
{
    /** @var array<int, int> Stores the terminal color rule as an array of ANSI codes. */
    protected array $rule;

    /**
     * Initializes the color rule.
     *
     * @param array<int, int> $rule An array of ANSI codes representing the color rule.
     * @return void
     */
    public function __construct(array $rule)
    {
        $this->rule = $rule;
    }

    /**
     * {@inheritdoc}
     */
    public function get(): array
    {
        return $this->rule;
    }

    /**
     * {@inheritdoc}
     */
    public function raw(): string
    {
        return implode(';', $this->rule);
    }
}
