<?php

declare(strict_types=1);

namespace Tests\Console;

use PHPUnit\Framework\TestCase;
use Omega\Console\Command;
use Omega\Console\Style\Style;
use Omega\Console\Traits\ValidateCommandTrait;
use Omega\Text\Str;
use Omega\Validator\Rule\ValidPool;

class ValidateTraitTest extends TestCase
{
    private $command;

    protected function setUp(): void
    {
        $this->command = new class(['php', 'omega', '--test', 'oke']) extends Command {
            use ValidateCommandTrait;

            public function main(): void
            {
                $this->initValidate($this->option_mapper);
                $this->getValidateMessage(new Style())->out(false);
            }

            protected function validateRule(ValidPool $rules): void
            {
                $rules('test')->required()->min_len(5);
            }
        };
    }

    /**
     * Test it can make text red
     *
     * @return void
     */
    public function testItCanMakeTextRed(): void
    {
        ob_start();
        $this->command->main();
        $out = ob_get_clean();

        $this->assertTrue(Str::contains($out, 'The Test field needs to be at least 5 characters'));
    }
}
