<?php

declare(strict_types=1);

namespace Tests\View\Templator;

use PHPUnit\Framework\TestCase;
use Omega\View\Templator;
use Omega\View\TemplatorFinder;

final class IfTest extends TestCase
{
    /**
     * @return void
     */
    public function testItCanRenderIf()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__]), __DIR__);
        $out       = $templator->templates('<html><head></head><body><h1>{% if ($true === true) %} show {% endif %}</h1><h1>{% if ($true === false) %} show {% endif %}</h1></body></html>');
        $this->assertEquals('<html><head></head><body><h1><?php if (($true === true) ): ?> show <?php endif; ?></h1><h1><?php if (($true === false) ): ?> show <?php endif; ?></h1></body></html>', $out);
    }

    /**
     * @return void
     */
    public function testItCanRenderIfElse()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__]), __DIR__);
        $out       = $templator->templates('<div>{% if ($condition) %}true case{% else %}false case{% endif %}</div>');
        $this->assertEquals('<div><?php if (($condition) ): ?>true case<?php else: ?>false case<?php endif; ?></div>', $out);
    }

    /**
     * @return void
     */
    public function testItCanRenderNestedIf()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__]), __DIR__);
        $template  = '<div>{% if ($level1) %}Level 1 true{% if ($level2) %}Level 2 true{% endif %}{% endif %}</div>';
        $expected  = '<div><?php if (($level1) ): ?>Level 1 true<?php if (($level2) ): ?>Level 2 true<?php endif; ?><?php endif; ?></div>';

        $this->assertEquals($expected, $templator->templates($template));
    }

    /**
     * @return void
     */
    public function testItCanRenderComplexNestedIfElse()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__]), __DIR__);
        $template  = '<div>{% if ($level1) %}Level 1 true{% if ($level2) %}Level 2 true{% else %}Level 2 false{% if ($level3) %}Level 3 true inside level 2 false{% endif %}{% endif %}{% else %}Level 1 false{% if ($otherCondition) %}Other condition true{% endif %}{% endif %}</div>';
        $expected  = '<div><?php if (($level1) ): ?>Level 1 true<?php if (($level2) ): ?>Level 2 true<?php else: ?>Level 2 false<?php if (($level3) ): ?>Level 3 true inside level 2 false<?php endif; ?><?php endif; ?><?php else: ?>Level 1 false<?php if (($otherCondition) ): ?>Other condition true<?php endif; ?><?php endif; ?></div>';

        $this->assertEquals($expected, $templator->templates($template));
    }

    /**
     * @return void
     */
    public function testItCanHandleMultipleIfBlocksWithNesting()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__]), __DIR__);
        $template  = '<div>{% if ($block1) %}Block 1 content{% if ($nested1) %}Nested 1{% endif %}{% endif %}{% if ($block2) %}Block 2 content{% if ($nested2) %}Nested 2{% if ($deepnested) %}Deep nested{% endif %}{% endif %}{% endif %}</div>';
        $expected  = '<div><?php if (($block1) ): ?>Block 1 content<?php if (($nested1) ): ?>Nested 1<?php endif; ?><?php endif; ?><?php if (($block2) ): ?>Block 2 content<?php if (($nested2) ): ?>Nested 2<?php if (($deepnested) ): ?>Deep nested<?php endif; ?><?php endif; ?><?php endif; ?></div>';

        $this->assertEquals($expected, $templator->templates($template));
    }
}
