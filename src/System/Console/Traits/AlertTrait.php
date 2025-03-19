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

namespace System\Console\Traits;

use System\Console\Style\Decorate;
use System\Console\Style\Style;

/**
 * AlertTrait class.
 *
 * The `AlertTrait` provides methods to render styled alerts such as info, warning,
 * failure, and success messages. It allows setting a margin for the alert messages
 * and supports custom styling for different alert types.
 *
 * @category   System
 * @package    Console
 * @subpackage Traits
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
trait AlertTrait
{
    /** @var int The left margin to apply before the alert message. */
    protected int $marginLeft = 0;

    /**
     * Sets the left margin for the alert messages.
     *
     * @param int $marginLeft The margin to set.
     * @return self
     */
    public function marginLeft(int $marginLeft): static
    {
        $this->marginLeft = $marginLeft;

        return $this;
    }

    /**
     * Renders an informational alert message with blue background.
     *
     * @param string $info The informational message to display.
     * @return Style The styled alert message.
     */
    public function info(string $info): Style
    {
        return (new Style())
            ->newLines()
            ->repeat(' ', $this->marginLeft)
            ->push(' info ')
            ->bold()
            ->rawReset([Decorate::RESET_BOLD_DIM])
            ->bgBlue()
            ->push(' ')
            ->push($info)
            ->newLines(2)
            ;
    }

    /**
     * Renders a warning alert message with yellow background.
     *
     * @param string $warn The warning message to display.
     * @return Style The styled alert message.
     */
    public function warn(string $warn): Style
    {
        return (new Style())
            ->newLines()
            ->repeat(' ', $this->marginLeft)
            ->push(' warn ')
            ->bold()
            ->rawReset([Decorate::RESET_BOLD_DIM])
            ->bgYellow()
            ->push(' ')
            ->push($warn)
            ->newLines(2)
            ;
    }

    /**
     * Renders a failure alert message with red background.
     *
     * @param string $fail The failure message to display.
     * @return Style The styled alert message.
     */
    public function fail(string $fail): Style
    {
        return (new Style())
            ->newLines()
            ->repeat(' ', $this->marginLeft)
            ->push(' fail ')
            ->bold()
            ->rawReset([Decorate::RESET_BOLD_DIM])
            ->bgRed()
            ->push(' ')
            ->push($fail)
            ->newLines(2)
            ;
    }

    /**
     * Renders a success alert message with green background.
     * This is similar to the "ok" message type.
     *
     * @param string $ok The success message to display.
     * @return Style The styled alert message.
     */
    public function ok(string $ok): Style
    {
        return (new Style())
            ->newLines()
            ->repeat(' ', $this->marginLeft)
            ->push(' ok ')
            ->bold()
            ->rawReset([Decorate::RESET_BOLD_DIM])
            ->bgGreen()
            ->push(' ')
            ->push($ok)
            ->newLines(2)
            ;
    }
}
