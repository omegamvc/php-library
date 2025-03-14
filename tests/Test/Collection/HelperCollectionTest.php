<?php

declare(strict_types=1);

namespace Test\Collection;

use System\Collection\Collection;
use PHPUnit\Framework\TestCase;

class HelperCollectionTest extends TestCase
{
    /** @test */
    public function testCollectionCreatesMutableCollection()
    {
        $collection = collection(['one' => 1, 'two' => 2]);

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertEquals([ 'one' => 1, 'two' => 2 ], $collection->all());
    }

    /** @test */
    public function testCollectionImmutableCreatesImmutableCollection()
    {
        $collection = collection_immutable(['one' => 1, 'two' => 2]);

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertEquals([ 'one' => 1, 'two' => 2 ], $collection->all());
    }

    /** @test */
    public function testCollectionCreatesEmptyCollection()
    {
        $collection = collection([]);

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertEmpty($collection->all());
    }
}
