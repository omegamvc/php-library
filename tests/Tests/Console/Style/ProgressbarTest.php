<?php

declare(strict_types=1);

namespace Tests\Console\Style;

use PHPUnit\Framework\TestCase;
use Omega\Console\Style\ProgressBar;
use Omega\Text\Str;

class ProgressbarTest extends TestCase
{
    /**
     * Test it can render progress bar.
     *
     * @return void
     */
    public function testItCanRenderProgressbar(): void
    {
        $progressbar = new ProgressBar(':progress');
        $progressbar->maks = 10;
        ob_start();
        foreach (range(1, 10) as $tick) {
            $progressbar->current++;
            $progressbar->tick();
        }
        $out = ob_get_clean();

        $this->assertTrue(Str::contains($out, '[=>------------------]'));
        $this->assertTrue(Str::contains($out, '[=========>----------]'));
        $this->assertTrue(Str::contains($out, '[====================]'));
    }

    /**
     * Test it can render progress bar using custom tick.
     *
     * @return void
     */
    public function testItCanRenderProgressBarUsingCustomTick(): void
    {
        $progressbar = new ProgressBar(':progress');
        $progressbar->maks = 10;
        ob_start();
        foreach (range(1, 10) as $tick) {
            $progressbar->current++;
            $progressbar->tickWith(':progress :costume', [
                ':costume' => fn(): string => "{$progressbar->current}/{$progressbar->maks}",
            ]);
        }
        $out = ob_get_clean();

        $this->assertTrue(Str::contains($out, '[=>------------------] 1/10'));
        $this->assertTrue(Str::contains($out, '[=========>----------] 5/10'));
        $this->assertTrue(Str::contains($out, '[====================] 10/10'));
    }
}
