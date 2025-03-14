<?php

declare(strict_types=1);

namespace Test\Collection;

use PHPUnit\Framework\TestCase;

class DataGetTest extends TestCase
{
    private array $array = [
        'awesome' => [
            'lang' => [
                'go',
                'rust',
                'php',
                'python',
                'js',
            ],
        ],
        'fav' => [
            'lang' => [
                'rust',
                'php',
            ],
        ],
        'dont_know' => ['lang_' => ['back_end' => ['erlang', 'h-lang']]],
        'one'       => ['two' => ['three' => ['four' => ['five' => 6]]]],
    ];

    /** @test */
    public function testItCanFindItemUsingDotKeys()
    {
        $this->assertEquals(6, data_get($this->array, 'one.two.three.four.five'));
    }

    /** @test */
    public function testItCanFindItemUsingDotKeysButDontExist()
    {
        $this->assertEquals('six', data_get($this->array, '1.2.3.4.5', 'six'));
    }

    /** @test */
    public function testItCanFindItemUsingDotKeysWithWildcard()
    {
        $this->assertEquals([
            ['go', 'rust', 'php', 'python', 'js'],
            ['rust', 'php'],
        ], data_get($this->array, '*.lang'));
    }

    /** @test */
    public function testItCanGeKeysAsInteger()
    {
        $array5 = ['foo', 'bar', 'baz'];
        $this->assertEquals('bar', data_get($array5, 1));
        $this->assertNull(data_get($array5, 3));
        $this->assertEquals('qux', data_get($array5, 3, 'qux'));
    }

    /** @test */
    public function testItReturnsDefaultWhenKeyIsNotFound()
    {
        $this->assertEquals('six', data_get($this->array, '1.2.3.4.5', 'six'));
    }

    /** @test */
    public function testItReturnsNullWhenKeyIsNotFoundAndNoDefault()
    {
        $this->assertNull(data_get($this->array, 'non.existing.key'));
    }

    /** @test */
    public function testItCanHandleWildcardWithNoMatch()
    {
        $this->assertEquals([], data_get($this->array, '*.non_existing_key'));
    }

    /** @test */
    public function testItCanReturnDefaultWithEmptyArray()
    {
        $this->assertEquals('default', data_get([], 'some.key', 'default'));
    }

    /** @test */
    public function testItHandlesContinueInWildcardSearch()
    {
        // Example array with nested array for wildcard
        $array = [
            ['lang' => ['go', 'rust', 'php']],
            ['lang' => ['python', 'js']],
            ['lang' => ['java', 'c++']],
        ];

        $this->assertEquals(['go', 'rust', 'php'], data_get($array, '*.lang'));
    }
}
