<?php

declare(strict_types=1);

namespace Tests\Time;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use function now;
use function time;

#[CoversNothing]
class HelperFunctionTest extends TestCase
{
    /**
     * Test it can use function helper.
     *
     * @return void
     */
    public function testItCanUseFunctionHelper(): void
    {
        $this->assertEquals(time(), now()->timestamp);
    }
}
