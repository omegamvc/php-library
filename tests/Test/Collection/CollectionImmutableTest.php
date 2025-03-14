<?php

declare(strict_types=1);

namespace Test\Collection;

use PHPUnit\Framework\TestCase;
use System\Collection\CollectionImmutable;
use System\Collection\Exceptions\NoModifyException;

use function array_keys;
use function array_values;
use function count;
use function in_array;
use function str_contains;

class CollectionImmutableTest extends TestCase
{
    /** @test */
    public function testItCollectionImmutableFunctionalWorkProperly(): void
    {
        $original = [
            'buah_1' => 'manga',
            'buah_2' => 'jeruk',
            'buah_3' => 'apel',
            'buah_4' => 'melon',
            'buah_5' => 'ambulant',
            'buah_6' => 'peer',
        ];
        $test = new CollectionImmutable($original);

        // getter
        $this->assertEquals('manga', $test->buah_1, 'add new item collection using __set');
        $this->assertEquals('manga', $test->get('buah_1'), 'add new item collection using set()');

        // cek array key
        $this->assertTrue($test->has('buah_1'), 'collection have item with key');

        // cek contain
        $this->assertTrue($test->contain('manga'), 'collection have item');

        // count
        $this->assertEquals(6, $test->count(), 'count item in collection');


        // count by
        $countIf = $test->countIf(function ($item) {
            // find letter contain 'e' letter
            return str_contains($item, 'e');
        });
        $this->assertEquals(4, $countIf, 'count item in collection with some condition');

        // first and last item cek
        $this->assertEquals('manga', $test->first('dog buah'), 'get first item in collection');
        $this->assertEquals('peer', $test->last('dog buah'), 'get last item in collection');

        // test array keys and values
        $keys  = array_keys($original);
        $items = array_values($original);
        $this->assertEquals($keys, $test->keys(), 'get all key in collection');
        $this->assertEquals($items, $test->items(), 'get all item value in collection');

        // each function
        $test->each(function ($item, $key) use ($original) {
            $this->assertTrue(in_array($item, $original), 'test each with value');
            $this->assertArrayHasKey($key, $original, 'test each with key');
        });

        // test the collection have item with e letter
        $some = $test->some(function ($item) {
            // find letter contain 'e' letter
            return str_contains($item, 'e');
        });
        $this->assertTrue($some, 'test the collection have item with "e" letter');

        // test the collection every item dont have 'x' letter
        $every = $test->every(function ($item) {
            // find letter contain 'x' letter
            return !str_contains($item, 'x');
        });
        $this->assertTrue($every, 'collection every item dont have "x" letter');

        // json output
        $json = json_encode($original);
        $this->assertJsonStringEqualsJsonString($test->json(), $json, 'collection convert to json string');
    }

    /** @test */
    public function testItCanActingLikeArray(): void
    {
        $coll = new CollectionImmutable(['one' => 1, 'two' => 2, 'three' => 3]);

        $this->assertArrayHasKey('one', $coll);
        $this->assertArrayHasKey('two', $coll);
        $this->assertArrayHasKey('three', $coll);
    }

    /** @test */
    public function testItCanDoLikeArray(): void
    {
        $arr  = ['one' => 1, 'two' => 2, 'three' => 3];
        $coll = new CollectionImmutable($arr);

        // get
        foreach ($arr as $key => $value) {
            $this->assertEquals($value, $coll[$key]);
        }

        // has
        $this->assertTrue(isset($coll['one']));
    }

    /** @test */
    public function testItCanByIterator(): void
    {
        $coll = new CollectionImmutable(['one' => 1, 'two' => 2, 'three' => 3]);

        foreach ($coll as $key => $value) {
            $this->assertEquals($value, $coll[$key]);
        }
    }

    /** @test */
    public function testItWillThrowExceptionWithSetMethod(): void
    {
        $coll = new CollectionImmutable(['one' => 1, 'two' => 2, 'three' => 3]);

        $this->expectException(NoModifyException::class);
        $coll['one'] = 4;
    }

    /** @test */
    public function testItWillThrowExceptionWithRemoveMethod(): void
    {
        $coll = new CollectionImmutable(['one' => 1, 'two' => 2, 'three' => 3]);

        $this->expectException(NoModifyException::class);
        unset($coll['one']);
    }

    /** @test */
    public function testItCanCountUsingCountFunction(): void
    {
        $coll = new CollectionImmutable(['one' => 1, 'two' => 2, 'three' => 3]);

        $this->assertCount(3, $coll);
        $this->assertEquals(3, count($coll));
    }

    /** @test */
    public function testItCanRandomizeItemsInCollection(): void
    {
        $arr  = ['one' => 1, 'two' => 2, 'three' => 3];
        $coll = new CollectionImmutable($arr);
        $item = $coll->rand();

        $this->assertTrue(
            in_array($item, array_values($arr))
        );
    }

    /** @test */
    public function testItCanGetCurrentNextPrev(): void
    {
        $arr  = ['one' => 1, 'two' => 2, 'three' => 3];
        $coll = new CollectionImmutable($arr);

        $this->assertEquals(1, $coll->current());
        $this->assertEquals(2, $coll->next());
        $this->assertEquals(1, $coll->prev());
    }

    /** @test */
    public function testItCanFilterUsingStrictType(): void
    {
        $coll = new CollectionImmutable(['one' => 1, 'two' => '2', 'three' => 3]);

        $this->assertTrue(
            $coll->contain(1)
        );
        $this->assertFalse(
            $coll->contain('1', true)
        );
    }

    /** @test */
    public function testItCanGetFirstKey(): void
    {
        $coll = new CollectionImmutable(['one' => 1, 'two' => '2', 'three' => 3]);

        $this->assertEquals('one', $coll->firstKey());
    }

    /** @test */
    public function testItCanGetFirstKeyNull(): void
    {
        $coll = new CollectionImmutable([]);

        $this->assertEquals(null, $coll->firstKey());
    }

    /** @test */
    public function testItCanGetLastKey(): void
    {
        $coll = new CollectionImmutable(['one' => 1, 'two' => '2', 'three' => 3]);

        $this->assertEquals('three', $coll->lastKey());
    }

    /** @test */
    public function testItCanGetLastKeyNull(): void
    {
        $coll = new CollectionImmutable([]);

        $this->assertEquals(null, $coll->lastKey());
    }

    /** @test */
    public function testItCanGetFirst(): void
    {
        $coll = new CollectionImmutable([10, 20, 30, 40, 50, 60, 70, 80, 90]);

        $this->assertEquals([10, 20], $coll->firsts(2));
    }

    /** @test */
    public function testItCanGetLasts(): void
    {
        $coll = new CollectionImmutable([10, 20, 30, 40, 50, 60, 70, 80, 90]);

        $this->assertEquals([80, 90], $coll->lasts(2));
    }

    /** @test */
    public function testItCanGetHighest(): void
    {
        $coll = new CollectionImmutable([10, 20, 30, 40, 50, 60, 70, 80, 90]);

        $this->assertEquals(90, $coll->max());

        $coll = new CollectionImmutable([
            ['rank' => 10],
            ['rank' => 50],
            ['rank' => 90],
        ]);

        $this->assertEquals(90, $coll->max('rank'));
    }

    /** @test */
    public function testItCanGetLowestValue(): void
    {
        $coll = new CollectionImmutable([10, 20, 30, 40, 50, 60, 70, 80, 90]);

        $this->assertEquals(10, $coll->min());

        $coll = new CollectionImmutable([
            ['rank' => 10],
            ['rank' => 50],
            ['rank' => 90],
        ]);

        $this->assertEquals(10, $coll->min('rank'));
    }

    /**
     * @test
     */
    public function testItCanPluck(): void
    {
        $coll = [
            ['user' => 'taylor'],
            ['user' => 'nuno'],
            ['user' => 'giovannini'],
        ];
        $coll = new CollectionImmutable($coll);

        $this->assertEquals(['taylor', 'nuno', 'giovannini'], $coll->pluck('user'));
    }

    /**
     * @test
     */
    public function testItCanPluckKey(): void
    {
        $coll = [
            ['id' => 1, 'user' => 'taylor'],
            ['id' => 2, 'user' => 'nuno'],
            ['id' => 3, 'user' => 'giovannini'],
        ];
        $coll = new CollectionImmutable($coll);

        $this->assertEquals([1 => 'taylor', 2 => 'nuno', 3 => 'giovannini'], $coll->pluck('user', 'id'));
    }
}
