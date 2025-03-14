<?php

declare(strict_types=1);

namespace Test\Collection;

use PHPUnit\Framework\TestCase;
use System\Collection\Collection;
use System\Collection\CollectionImmutable;

use function array_filter;
use function array_keys;
use function array_map;
use function array_reverse;
use function array_values;
use function in_array;
use function json_encode;
use function str_contains;

class CollectionTest extends TestCase
{
    /** @test */
    public function testItCollectionFunctionalWorKProperly(): void
    {
        $original = [
            'buah_1' => 'manga',
            'buah_2' => 'jeruk',
            'buah_3' => 'apel',
            'buah_4' => 'melon',
            'buah_5' => 'rambutan',
            'buah_6' => 'peer',
        ];
        $test = new Collection($original);

        // getter
        $this->assertEquals('manga', $test->buah_1, 'add new item collection using __set');
        $this->assertEquals('manga', $test->get('buah_1'), 'add new item collection using set()');

        // add new item
        $test->set('buah_7', 'kelengkeng');
        $test->buah_8 = 'cherry';
        $this->assertEquals('cherry', $test->buah_8, 'get item collection using __get');
        $this->assertEquals('kelengkeng', $test->get('buah_7'), 'get item collection using get()');

        // rename item
        $test->set('buah_7', 'durian');
        $test->buah_8 = 'jolly';
        $this->assertEquals('jolly', $test->buah_8, 'replace exists item collection using __get');
        $this->assertEquals('durian', $test->get('buah_7'), 'replace exists item collection using get()');

        // cek array key
        $this->assertTrue($test->has('buah_1'), 'collection have item with key');

        // cek contain
        $this->assertTrue($test->contain('manga'), 'collection have item');

        // remove item
        $test->remove('buah_2');
        $this->assertFalse($test->has('buah_2'), 'remove some item using key');

        // reset to origin
        $test->replace($original);

        // count
        $this->assertEquals(6, $test->count(), 'count item in collection');

        // count by
        $countIf = $test->countIf(function ($item) {
            // find letter contain 'e' letter
            return str_contains($item, 'e');
        });
        $this->assertEquals(4, $countIf, 'count item in collection with some condition');

        // first and last item cek
        $this->assertEquals('manga', $test->first('bukan buah'), 'get first item in collection');
        $this->assertEquals('peer', $test->last('bukan buah'), 'get last item in collection');

        // test clear and empty cek
        $this->assertFalse($test->isEmpty());
        $test->clear();
        $this->assertTrue($test->isEmpty(), 'cek collection empty');
        // same with origin
        $test->replace($original);
        $this->assertEquals($test->all(), $original, 'replace axis collection with new data');

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

        // map function
        $test->map(fn ($item) => ucfirst($item));
        $copyOrigin = array_map(fn ($item) => ucfirst($item), $original);
        $this->assertEquals($test->all(), $copyOrigin, 'replace some/all item using map');
        $test->replace($original);

        // filter function
        $test->filter(function ($item) {
            // find letter contain 'e' letter
            return str_contains($item, 'e');
        });
        $copyOrigin = array_filter($original, function ($item) {
            // find letter contain 'e' letter
            return str_contains($item, 'e');
        });
        $this->assertEquals($test->all(), $copyOrigin, 'filter item in collection');
        $test->replace($original);

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

        // collection reverse
        $copyOrigin = $original;
        $this->assertEquals(
            $test->reverse()->all(),
            array_reverse($copyOrigin),
            'test reverse collection'
        );
        $test->replace($original);

        // sort collection
        // sort asc
        $this->assertEquals(
            'apel',
            $test->sort()->first(),
            'testing sort asc collection'
        );
        // sort desc
        $this->assertEquals(
            'rambutan',
            $test->sortDesc()->first(),
            'testing sort desc collection'
        );
        // sort using callback
        $test->sortBy(function ($a, $b) {
            if ($a == $b) {
                return 0;
            }

            return ($a < $b) ? -1 : 1;
        });
        $this->assertEquals(
            'apel',
            $test->first(),
            'sort using user define ascending'
        );
        $test->sortByDesc(function ($a, $b) {
            if ($a == $b) {
                return 0;
            }

            return ($a < $b) ? -1 : 1;
        });
        $this->assertEquals(
            'rambutan',
            $test->first(),
            'sort using user define descending'
        );

        // sort collection by key
        $this->assertEquals(
            'manga',
            $test->sortKey()->first(),
            'sort collection asc with key'
        );
        $this->assertEquals(
            'peer',
            $test->sortKeyDesc()->first(),
            'sort collection desc with key'
        );
        $test->replace($original);

        // clone collection
        $this->assertEquals(
            $test->clone()->reverse()->first(),
            $test->last(),
            'clone collection without interrupt original'
        );

        // reject
        $copy_origin = $original;
        unset($copy_origin['buah_2']);
        $this->assertEquals(
            $test->reject(fn ($item) => $item == 'jeruk')->all(),
            $copy_origin,
            'its like filter but the opposite'
        );

        // chunk
        $chunk = $test->clone()->chunk(3)->all();
        $this->assertEquals(
            [
                ['buah_1' => 'manga', 'buah_3' => 'apel', 'buah_4' => 'melon'],
                ['buah_5' => 'rambutan', 'buah_6' => 'peer'],
            ],
            $chunk,
            'chunk to 3'
        );

        // split
        $split = $test->clone()->split(3)->all();
        $this->assertEquals(
            [
                ['buah_1' => 'manga', 'buah_3' => 'apel'],
                ['buah_4' => 'melon', 'buah_5' => 'rambutan'],
                ['buah_6' => 'peer'],
            ],
            $split,
            'split to 2'
        );

        $only = $test->clone()->only(['buah_1', 'buah_5']);
        $this->assertEquals(
            ['buah_1' => 'manga', 'buah_5' => 'rambutan'],
            $only->all(),
            'show only some'
        );

        $except = $test->clone()->except(['buah_3', 'buah_4', 'buah_6']);
        $this->assertEquals(
            ['buah_1' => 'manga', 'buah_5' => 'rambutan'],
            $except->all(),
            'show list with except'
        );

        // flatten
        $array_nesting = [
            'first' => ['buah_1' => 'manga', ['buah_2' => 'jeruk', 'buah_3' => 'apel', 'buah_4' => 'melon']],
            'mid'   => ['buah_4' => 'melon', ['buah_5' => 'rambutan']],
            'last'  => ['buah_6' => 'peer'],
        ];
        $flatten = new Collection($array_nesting);
        $this->assertEquals(
            $original,
            $flatten->flatten()->all(),
            'flatten nesting array'
        );
    }

    /** @test */
    public function testItCollectionChainWorkGreat(): void
    {
        $origin     = [0, 1, 2, 3, 4];
        $collection = new Collection($origin);

        $chain = $collection
            ->add($origin)
            ->remove(0)
            ->set(0, 0)
            ->clear()
            ->replace($origin)
            ->each(fn ($el) => in_array($el, $origin))
            ->map(fn ($el) => $el + 100 - (2 * 50)) // equal +0
            ->filter(fn ($el) => $el > -1)
            ->sort()
            ->sortDesc()
            ->sortKey()
            ->sortKeyDesc()
            ->sortBy(function ($a, $b) {
                if ($a == $b) {
                    return 0;
                }

                return ($a < $b) ? -1 : 1;
            })
            ->sortByDesc(function ($a, $b) {
                if ($b == $a) {
                    return 0;
                }

                return ($b < $a) ? -1 : 1;
            })
            ->all()
        ;

        $this->assertEquals($chain, $origin, 'all collection with chain is work');
    }

    /** @test */
    public function testItCanAddCollectionFromCollection()
    {
        $arr_1 = ['a' => 'b'];
        $arr_2 = ['c' => 'd'];

        $collect_1 = new Collection($arr_1);
        $collect_2 = new CollectionImmutable($arr_2);

        $collect = new Collection([]);
        $collect->ref($collect_1)->ref($collect_2);

        $this->assertEquals(['a' => 'b', 'c' => 'd'], $collect->all());
    }

    /** @test */
    public function testItCanActingLikeArray()
    {
        $coll = new Collection(['one' => 1, 'two' => 2, 'three' => 3]);

        $this->assertArrayHasKey('one', $coll);
        $this->assertArrayHasKey('two', $coll);
        $this->assertArrayHasKey('three', $coll);
    }

    /** @test */
    public function testItCanDoLikeArray()
    {
        $arr  = ['one' => 1, 'two' => 2, 'three' => 3];
        $coll = new Collection($arr);

        // get
        foreach ($arr as $key => $value) {
            $this->assertEquals($value, $coll[$key]);
        }

        // set
        $coll['four'] = 4;
        $this->assertArrayHasKey('four', $coll);

        // has
        $this->assertTrue(isset($coll['four']));

        // unset
        unset($coll['four']);
        $this->assertEquals($arr, $coll->all());
    }

    /** @test */
    public function testItCanByIterator()
    {
        $coll = new Collection(['one' => 1, 'two' => 2, 'three' => 3]);

        foreach ($coll as $key => $value) {
            $this->assertEquals($value, $coll[$key]);
        }
    }

    /** @test */
    public function testItCanByShuffle()
    {
        $arr  = ['one' => 1, 'two' => 2, 'three' => 3];
        $coll = new Collection($arr);

        $coll->shuffle();

        foreach ($arr as $key => $val) {
            $this->assertArrayHasKey($key, $coll);
        }
    }

    /** @test */
    public function testItCanMapWithKeys()
    {
        $arr = new Collection([
            [
                'name'  => 'taylor',
                'email' => 'taylor@laravel.com',
            ], [
                'name'  => 'giovannini',
                'email' => 'giovannini@gmail.com',
            ],
        ]);

        $assocBy = $arr->assocBy(fn ($item) => [$item['name'] => $item['email']]);

        $this->assertEquals([
            'taylor'     => 'taylor@laravel.com',
            'giovannini' => 'giovannini@gmail.com',
        ], $assocBy->toArray());
    }

    /** @test */
    public function testItCanCloneCollection()
    {
        $ori = new Collection([
            'one' => 'one',
            'two' => [
                'one',
                'two' => [1, 2],
            ],
            'three' => new Collection([]),
        ]);

        $clone = clone $ori;

        $ori->set('one', 'uno');
        $this->assertEquals('one', $clone->get('one'));

        $clone->set('one', 1);
        $this->assertEquals('uno', $ori->get('one'));
    }

    /** @test */
    public function testItCanGetSumUsingReduce()
    {
        $collection = new Collection([1, 2, 3, 4]);

        $sum = $collection->reduce(fn ($carry, $item) => $carry + $item);

        $this->assertTrue($sum === 10);
    }

    /** @test */
    public function testItCanGetTakeFirst()
    {
        $coll = new Collection([10, 20, 30, 40, 50, 60, 70, 80, 90]);

        $this->assertEquals([10, 20], $coll->take(2)->toArray());
    }

    /** @test */
    public function testItCanGetTakeLast()
    {
        $coll = new Collection([10, 20, 30, 40, 50, 60, 70, 80, 90]);

        $this->assertEquals([80, 90], $coll->take(-2)->toArray());
    }

    /** @test */
    public function testItCanPushNewItem()
    {
        $coll = new Collection([10, 20, 30, 40, 50, 60, 70, 80, 90]);
        $coll->push(100);

        $this->assertTrue(in_array(100, $coll->toArray()));
    }

    /** @test */
    public function testItCanGetDiff()
    {
        $coll = new Collection([1, 2, 3, 4, 5]);
        $coll->diff([2, 4, 6, 8]);

        $this->assertEquals([1, 3, 5], $coll->items());
    }

    /** @test */
    public function testItCanGetDiffUsingKey()
    {
        $coll = new Collection([
            'buah_1' => 'manga',
            'buah_2' => 'jeruk',
            'buah_3' => 'apel',
            'buah_4' => 'melon',
            'buah_5' => 'rambutan',
        ]);
        $coll->diffKeys([
            'buah_2' => 'orange',
            'buah_4' => 'water melon',
            'buah_6' => 'six',
            'buah_8' => 'eight',
        ]);

        $this->assertEquals([
            'buah_1' => 'manga',
            'buah_3' => 'apel',
            'buah_5' => 'rambutan',
        ], $coll->toArray());
    }

    /** @test */
    public function testItCanGetDiffUsingAssoc()
    {
        $coll = new Collection([
            'color'   => 'green',
            'type'    => 'library',
            'version' => 0,
        ]);
        $coll->diffAssoc([
            'color'   => 'orange',
            'type'    => 'framework',
            'version' => 10,
            'used'    => 100,
        ]);

        $this->assertEquals([
            'color'   => 'green',
            'type'    => 'library',
            'version' => 0,
        ], $coll->toArray());
    }

    /** @test */
    public function testItCanGetComplement()
    {
        $coll = new Collection([1, 2, 3, 4, 5]);
        $coll->complement([2, 4, 6, 8]);

        $this->assertEquals([6, 8], $coll->items());
    }

    /** @test */
    public function testItCanGetComplementUsingKey()
    {
        $coll = new Collection([
            'buah_1' => 'manga',
            'buah_2' => 'jeruk',
            'buah_3' => 'apel',
            'buah_4' => 'melon',
            'buah_5' => 'rambutan',
        ]);
        $coll->complementKeys([
            'buah_2' => 'orange',
            'buah_4' => 'water melon',
            'buah_6' => 'six',
            'buah_8' => 'eight',
        ]);

        $this->assertEquals([
            'buah_6' => 'six',
            'buah_8' => 'eight',
        ], $coll->toArray());
    }

    /** @test */
    public function testItCanGetComplementUsingAssoc()
    {
        $coll = new Collection([
            'color'   => 'green',
            'type'    => 'library',
            'version' => 0,
        ]);
        $coll->complementAssoc([
            'color'   => 'orange',
            'type'    => 'framework',
            'version' => 10,
            'used'    => 100,
        ]);

        $this->assertEquals([
            'color'   => 'orange',
            'type'    => 'framework',
            'version' => 10,
            'used'    => 100,
        ], $coll->toArray());
    }

    /**
     * @test
     */
    public function testItCanGetFilteredUsingWhere()
    {
        $data = [
            ['user' => 'user1', 'age' => 10],
            ['user' => 'user2', 'age' => 12],
            ['user' => 'user3', 'age' => 10],
            ['user' => 'user4', 'age' => 13],
            ['user' => 'user5', 'age' => 14],
        ];
        $equal = (new Collection($data))->where('age', '=', '13');
        $this->assertEquals([
            3 => ['user' => 'user4', 'age' => 13],
        ], $equal->toArray());

        $identical = (new Collection($data))->where('age', '===', 13);
        $this->assertEquals([
            3 => ['user' => 'user4', 'age' => 13],
        ], $identical->toArray());

        $notEqual = (new Collection($data))->where('age', '!=', '13');
        $this->assertEquals([
            ['user' => 'user1', 'age' => 10],
            ['user' => 'user2', 'age' => 12],
            ['user' => 'user3', 'age' => 10],
            4       => ['user' => 'user5', 'age' => 14],
        ], $notEqual->toArray());

        $notEqualIdentical = (new Collection($data))->where('age', '!==', 13);
        $this->assertEquals([
            ['user' => 'user1', 'age' => 10],
            ['user' => 'user2', 'age' => 12],
            ['user' => 'user3', 'age' => 10],
            4       => ['user' => 'user5', 'age' => 14],
        ], $notEqualIdentical->toArray());

        $greaterThan = (new Collection($data))->where('age', '>', 13);
        $this->assertEquals([
            4 => ['user' => 'user5', 'age' => 14],
        ], $greaterThan->toArray());

        $greaterThanEqual = (new Collection($data))->where('age', '>=', 13);
        $this->assertEquals([
            3 => ['user' => 'user4', 'age' => 13],
            4 => ['user' => 'user5', 'age' => 14],
        ], $greaterThanEqual->toArray());

        $lessThan = (new Collection($data))->where('age', '<', 13);
        $this->assertEquals([
            ['user' => 'user1', 'age' => 10],
            ['user' => 'user2', 'age' => 12],
            ['user' => 'user3', 'age' => 10],
        ], $lessThan->toArray());

        $lessThanEqual = (new Collection($data))->where('age', '<=', 13);
        $this->assertEquals([
            ['user' => 'user1', 'age' => 10],
            ['user' => 'user2', 'age' => 12],
            ['user' => 'user3', 'age' => 10],
            ['user' => 'user4', 'age' => 13],
        ], $lessThanEqual->toArray());
    }

    /**
     * @test
     */
    public function testItCanFilterDataUsingWhereIn()
    {
        $data = [
            ['user' => 'user1', 'age' => 10],
            ['user' => 'user2', 'age' => 12],
            ['user' => 'user3', 'age' => 10],
            ['user' => 'user4', 'age' => 13],
            ['user' => 'user5', 'age' => 14],
        ];

        $wherein = (new Collection($data))->whereIn('age', [10, 12]);
        $this->assertEquals([
            ['user' => 'user1', 'age' => 10],
            ['user' => 'user2', 'age' => 12],
            ['user' => 'user3', 'age' => 10],
        ], $wherein->toArray());
    }

    /**
     * @test
     */
    public function testItCanFilterDataUsingWhereNotIn()
    {
        $data = [
            ['user' => 'user1', 'age' => 10],
            ['user' => 'user2', 'age' => 12],
            ['user' => 'user3', 'age' => 10],
            ['user' => 'user4', 'age' => 13],
            ['user' => 'user5', 'age' => 14],
        ];

        $wherein = (new Collection($data))->whereNotIn('age', [13, 14]);
        $this->assertEquals([
            ['user' => 'user1', 'age' => 10],
            ['user' => 'user2', 'age' => 12],
            ['user' => 'user3', 'age' => 10],
        ], $wherein->toArray());
    }
}
