<?php

declare(strict_types=1);

namespace Tests\Unit\Collection;

use Covaleski\Collection\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Collection::class)]
final class CollectionTest extends TestCase
{
    /**
     * Associative array collection.
     */
    protected Collection $associative;

    /**
     * List array collection.
     */
    protected Collection $list;

    /**
     * Object collection.
     */
    protected Collection $object;

    /**
     * Collection data sources.
     * 
     * @var array<string, array|object>
     */
    protected array $sources;

    /**
     * Provide arguments to get collection columns.
     */
    public static function provideColumnArguments(): array
    {
        return [
            [
                'call_sign',
                null,
                ['FBZ5902', 'TAM3476', 'AZU8725', 'ARG1152', 'TAM3322'],
            ],
            [
                'origin',
                'call_sign',
                [
                    'FBZ5902' => 'AEP',
                    'TAM3476' => 'CWB',
                    'AZU8725' => 'MVD',
                    'ARG1152' => 'GRU',
                    'TAM3322' => 'GRU',
                ],
            ],
        ];
    }

    /**
     * Test if can access all the collection values.
     */
    public function testAccessesAllValues(): void
    {
        $this->assertSame(
            $this->sources['associative'],
            $this->associative->all(),
        );
        $this->assertSame(
            $this->sources['list'],
            $this->list->all(),
        );
        $this->assertSame(
            $this->sources['object'],
            $this->object->all(),
        );
    }

    /**
     * Test if can access values using keys.
     */
    public function testAccessesValuesByKeys(): void
    {
        $this->assertSame('A320', $this->associative->get('model'));
        $this->assertSame('Airbus', $this->associative->get('manufacturer'));
        $this->assertSame(2, $this->associative->get('crew'));
        $this->assertSame(1988, $this->associative->get('introduction'));
        $this->assertNull($this->object->get('engines'));
        $this->assertSame(2, $this->object->get('engines', 2));
        $this->assertSame(
            'Aeroporto Internacional Salgado Filho',
            $this->object->get('name'),
        );
        $this->assertSame('POA', $this->object->get('iata'));
        $this->assertSame('SBPA', $this->object->get('icao'));
        $this->assertSame('Porto Alegre', $this->object->get('city'));
        $this->assertNull($this->object->get('country'));
        $this->assertSame('Brazil', $this->object->get('country', 'Brazil'));
    }

    /**
     * Test if can access values using positions.
     */
    public function testAccessesValuesByPosition(): void
    {
        $this->assertSame('A320', $this->associative->nth(0));
        $this->assertSame('Airbus', $this->associative->nth(1));
        $this->assertSame(2, $this->associative->nth(2));
        $this->assertSame(1988, $this->associative->nth(3));
        $this->assertNull($this->associative->nth(4));
        $this->assertSame('???', $this->associative->nth(4, '???'));
        $this->assertSame('A320', $this->associative->nth(-4));
        $this->assertSame('Airbus', $this->associative->nth(-3));
        $this->assertSame(2, $this->associative->nth(-2));
        $this->assertSame(1988, $this->associative->nth(-1));
        $this->assertNull($this->associative->nth(-5));
        $this->assertSame('???', $this->associative->nth(-5, '???'));
        $this->assertSame('A320', $this->associative->first());
        $this->assertSame(1988, $this->associative->last());
    }

    /**
     * Test if can access values using array notation.
     */
    public function testAccessesValuesLikeArrays(): void
    {
        $this->assertSame('A320', $this->associative['model']);
        $this->assertSame('Airbus', $this->associative['manufacturer']);
        $this->assertSame(2, $this->associative['crew']);
        $this->assertSame(1988, $this->associative['introduction']);
        $this->assertTrue(isset($this->associative['model']));
        unset($this->associative['model']);
        $this->assertFalse(isset($this->associative['model']));
        $this->associative['manufacturer'] = 'Boeing';
        $this->assertSame('Boeing', $this->associative['manufacturer']);
        $this->assertSame(
            'Aeroporto Internacional Salgado Filho',
            $this->object['name'],
        );
        $this->assertSame('POA', $this->object['iata']);
        $this->assertSame('SBPA', $this->object['icao']);
        $this->assertSame('Porto Alegre', $this->object['city']);
        $this->assertTrue(isset($this->object['iata']));
        unset($this->object['iata']);
        $this->assertFalse(isset($this->object['iata']));
        $this->object['city'] = 'P. Alegre';
        $this->assertSame('P. Alegre', $this->object['city']);
    }

    public function testAddsAndRemovesValuesLikeArrays(): void
    {
        $array = new Collection(['John', 'Mary', 'Paul', 'Jane']);
        $this->assertSame('Jane', $array->pop());
        $this->assertSame('John', $array->shift());
        $this->assertEquals(
            ['James', 'Meghan', 'Mary', 'Paul', 'Linda', 'Jack'],
            $array
                ->push('Linda', 'Jack')
                ->unshift('James', 'Meghan')
                ->all(),
        );
        $this->assertSame('James', $array->shift());
        $this->assertSame('Meghan', $array->shift());
        $this->assertSame('Mary', $array->shift());
        $this->assertSame('Jack', $array->pop());
        $this->assertSame('Linda', $array->pop());
        $this->assertSame('Paul', $array->pop());
        $this->assertNull($array->shift());
        $this->assertNull($array->pop());
        $object = new Collection((object) ['Ford', 'Opel', 'Renault', 'Fiat']);
        $this->assertSame('Fiat', $object->pop());
        $this->assertSame('Ford', $object->shift());
        $this->assertEquals(
            (object) ['Toyota', 'Subaru', 'Opel', 'Renault', 'BMW', 'BYD'],
            $object
                ->push('BMW', 'BYD')
                ->unshift('Toyota', 'Subaru')
                ->all(),
        );
        $this->assertSame('Toyota', $object->shift());
        $this->assertSame('Subaru', $object->shift());
        $this->assertSame('Opel', $object->shift());
        $this->assertSame('BYD', $object->pop());
        $this->assertSame('BMW', $object->pop());
        $this->assertSame('Renault', $object->pop());
        $this->assertNull($object->shift());
        $this->assertNull($object->pop());
        $array = new Collection(['Apple', 'Banana', 'foo' => 'Orange']);
        $this->assertSame('Apple', $array->shift());
        $array->unshift('Watermelon');
        $this->assertEquals(
            ['Watermelon', 'Banana', 'foo' => 'Orange'],
            $array->all(),
        );
        $object = new Collection((object) ['A', 'B', 'foo' => 'C', 'D']);
        $this->assertSame('A', $object->shift());
        $object->unshift('Z');
        $this->assertEquals(
            (object) ['Z', 'B', 'foo' => 'C', 'D'],
            $object->all(),
        );
    }

    /**
     * Test if can create copies that DON'T reference the original values.
     */
    public function testCreatesCopies(): void
    {
        $copy = $this->associative->copy();
        $this->assertEquals(
            $this->associative->all(),
            $copy->all(),
        );
        $this->assertSame(
            $this->associative->all(),
            $copy->all(),
        );
        $copy = $this->list->copy();
        $this->assertEquals(
            $this->list->all(),
            $copy->all(),
        );
        $this->assertSame(
            $this->list->all(),
            $copy->all(),
        );
        $copy = $this->object->copy();
        $this->assertEquals(
            $this->object->all(),
            $copy->all(),
        );
        $this->assertNotSame(
            $this->object->all(),
            $copy->all(),
        );
    }

    /**
     * Test if can create collections by filtering the previous one.
     */
    public function testFiltersValues(): void
    {
        $this->assertEquals(
            [
                3 => (object) [
                    'id' => 4,
                    'call_sign' => 'ARG1152',
                    'origin' => 'GRU',
                    'destination' => 'AEP',
                ],
                4 => (object) [
                    'id' => 5,
                    'call_sign' => 'TAM3322',
                    'origin' => 'GRU',
                    'destination' => 'CXJ',
                ],
            ],
            $this->list
                ->filter(function ($value, $key) {
                    $this->assertIsInt($key);
                    $this->assertIsObject($value);
                    return $value->origin === 'GRU';
                })
                ->all(),
        );
    }

    /**
     * Test if can filter values by column.
     */
    public function testFiltersValuesByColumn(): void
    {
        $this->sources['list'][0]->departed_at = '2025-01-21 12:14:52';
        $this->sources['list'][1]->departed_at = '2025-01-21 16:41:59';
        $this->sources['list'][2]->departed_at = '2025-01-22 08:12:30';
        $this->sources['list'][3]->departed_at = '2025-01-22 17:18:19';
        $this->sources['list'][4]->departed_at = '2025-01-24 20:04:01';
        $this->assertSame(
            [
                3 => $this->sources['list'][3],
                4 => $this->sources['list'][4],
            ],
            $this->list
                ->where('origin', '=', 'GRU')
                ->all(),
        );
        $this->assertSame(
            [
                0 => $this->sources['list'][0],
                1 => $this->sources['list'][1],
                2 => $this->sources['list'][2],
            ],
            $this->list
                ->where('origin', '!=', 'GRU')
                ->all(),
        );
        $this->assertSame(
            [
                2 => $this->sources['list'][2],
            ],
            $this->list
                ->where('departed_at', '>', '2025-01-21 16:41:59')
                ->where('departed_at', '<', '2025-01-22 17:18:19')
                ->all(),
        );
        $this->assertSame(
            [
                1 => $this->sources['list'][1],
                2 => $this->sources['list'][2],
                3 => $this->sources['list'][3],
            ],
            $this->list
                ->where('departed_at', '>=', '2025-01-21 16:41:59')
                ->where('departed_at', '<=', '2025-01-22 17:18:19')
                ->all(),
        );
        $this->assertSame(
            [
                1 => $this->sources['list'][1],
                4 => $this->sources['list'][4],
            ],
            $this->list
                ->where('call_sign', '^=', 'TAM')
                ->all(),
        );
        $this->assertSame(
            [
                0 => $this->sources['list'][0],
                3 => $this->sources['list'][3],
                4 => $this->sources['list'][4],
            ],
            $this->list
                ->where('call_sign', '$=', '2')
                ->all(),
        );
        $this->assertSame(
            [
                1 => $this->sources['list'][1],
                2 => $this->sources['list'][2],
            ],
            $this->list
                ->where('call_sign', '*=', '7')
                ->all(),
        );
    }

    /**
     * Test if can capture all values in the specified list "column".
     */
    #[DataProvider('provideColumnArguments')]
    public function testGetsColumns(
        null|int|string $column_key,
        null|int|string $index_key,
        array $expected,
    ): void {
        $this->assertEquals(
            $expected,
            $this->list->column($column_key, $index_key)->all(),
        );
    }

    /**
     * Test if can create collections with the keys from the previous one.
     */
    public function testGetsKeys(): void
    {
        $this->assertEquals(
            ['model', 'manufacturer', 'crew', 'introduction'],
            $this->associative->keys()->all(),
        );
        $this->assertEquals(
            [0, 1, 2, 3, 4],
            $this->list->keys()->all(),
        );
        $this->assertEquals(
            ['name', 'iata', 'icao', 'city'],
            $this->object->keys()->all(),
        );
    }

    /**
     * Test if can create collections with the values from the previous one.
     */
    public function testGetsValues(): void
    {
        $this->assertEquals(
            ['A320', 'Airbus', 2, 1988],
            $this->associative->values()->all(),
        );
        $this->assertEquals(
            [
                'Aeroporto Internacional Salgado Filho',
                'POA',
                'SBPA',
                'Porto Alegre',
            ],
            $this->object->values()->all(),
        );
        $this->assertEquals(
            $this->sources['list'],
            $this->list->values()->all(),
        );
    }

    /**
     * Test if can count stored values.
     */
    public function testIsCountable(): void
    {
        $this->assertSame(4, $this->associative->count());
        $this->assertSame(4, count($this->associative));
        $this->assertSame(4, $this->object->count());
        $this->assertSame(4, count($this->object));
        $this->assertSame(5, $this->list->count());
        $this->assertSame(5, count($this->list));
    }

    /**
     * Test if can iterate stored values.
     */
    public function testIsIterable(): void
    {
        $keys = array_keys($this->sources['associative']);
        $values = array_values($this->sources['associative']);
        $i = 0;
        foreach ($this->associative as $key => $value) {
            $this->assertSame($keys[$i], $key);
            $this->assertSame($values[$i], $value);
            $i++;
        }
        $vars = get_object_vars($this->sources['object']);
        $keys = array_map('strval', array_keys($vars));
        $values = array_values($vars);
        $i = 0;
        foreach ($this->object as $key => $value) {
            $this->assertSame($keys[$i], $key);
            $this->assertSame($values[$i], $value);
            $i++;
        }
        $keys = array_keys($this->sources['list']);
        $values = array_values($this->sources['list']);
        $i = 0;
        foreach ($this->list as $key => $value) {
            $this->assertSame($keys[$i], $key);
            $this->assertSame($values[$i], $value);
            $i++;
        }
    }

    /**
     * Test if can run callbacks for each element.
     */
    public function testIteratesValues(): void
    {
        $count_a = 0;
        $count_b = 0;
        $this->assertSame($this->object, $this->object->walk(
            function ($value, $key) use (&$count_a) {
                $count_a++;
                $this->assertIsString($key);
                $this->assertIsString($value);
            },
        ));
        $this->assertSame($this->list, $this->list->walk(
            function ($value, $key) use (&$count_b) {
                $count_b++;
                $this->assertIsInt($key);
                $this->assertIsObject($value);
            },
        ));
        $this->assertSame(4, $count_a);
        $this->assertSame(5, $count_b);
    }

    /**
     * Test if can create collections iterating the previous one.
     */
    public function testMapsValues(): void
    {
        $this->assertEquals(
            [
                'name=Aeroporto Internacional Salgado Filho',
                'iata=POA',
                'icao=SBPA',
                'city=Porto Alegre',
            ],
            $this->object
                ->map(function ($value, $key) {
                    $this->assertIsString($key);
                    $this->assertIsString($value);
                    return "{$key}={$value}";
                })
                ->all(),
        );
        $this->assertEquals(
            [
                '#0: AEP -> GIG',
                '#1: CWB -> POA',
                '#2: MVD -> CWB',
                '#3: GRU -> AEP',
                '#4: GRU -> CXJ',
            ],
            $this->list
                ->map(function ($value, $key) {
                    $this->assertIsInt($key);
                    $this->assertIsObject($value);
                    return "#{$key}: {$value->origin} -> {$value->destination}";
                })
                ->all(),
        );
    }

    /**
     * Test if can concatenate associative array collections.
     */
    public function testMergesAssociativeArrays(): void
    {
        $a = new Collection([
            'model' => 'A-320',
            'role' => 'Narrow-body airliner',
        ]);
        $b = new Collection((object) [
            'manufacturer' => 'Airbus Group',
            'status' => 'In service',
        ]);
        $this->assertEquals(
            [
                'model' => 'A-320',
                'manufacturer' => 'Airbus Group',
                'crew' => 2,
                'introduction' => 1988,
                'role' => 'Narrow-body airliner',
                'status' => 'In service',
            ],
            $this->associative->merge($a, $b)->all(),
        );
    }

    /**
     * Test if can concatenate list collections.
     */
    public function testMergesLists(): void
    {
        $collection = new Collection([
            (object) [
                'id' => 6,
                'call_sign' => 'GLO1885',
                'origin' => 'GRU',
                'destination' => 'POA',
            ],
            (object) [
                'id' => 7,
                'call_sign' => 'TAM3602',
                'origin' => 'GIG',
                'destination' => 'POA',
            ],
        ]);
        $this->assertEquals(
            [
                (object) [
                    'id' => 1,
                    'call_sign' => 'FBZ5902',
                    'origin' => 'AEP',
                    'destination' => 'GIG',
                ],
                (object) [
                    'id' => 2,
                    'call_sign' => 'TAM3476',
                    'origin' => 'CWB',
                    'destination' => 'POA',
                ],
                (object) [
                    'id' => 3,
                    'call_sign' => 'AZU8725',
                    'origin' => 'MVD',
                    'destination' => 'CWB',
                ],
                (object) [
                    'id' => 4,
                    'call_sign' => 'ARG1152',
                    'origin' => 'GRU',
                    'destination' => 'AEP',
                ],
                (object) [
                    'id' => 5,
                    'call_sign' => 'TAM3322',
                    'origin' => 'GRU',
                    'destination' => 'CXJ',
                ],
                (object) [
                    'id' => 6,
                    'call_sign' => 'GLO1885',
                    'origin' => 'GRU',
                    'destination' => 'POA',
                ],
                (object) [
                    'id' => 7,
                    'call_sign' => 'TAM3602',
                    'origin' => 'GIG',
                    'destination' => 'POA',
                ],
            ],
            $this->list->merge($collection)->all(),
        );
    }

    /**
     * Test if can concatenate object collections.
     */
    public function testMergesObjects(): void
    {
        $a = new Collection((object) [
            'name' => 'Aeroporto Internacional de Porto Alegre',
            'country' => 'Brazil',
        ]);
        $b = new Collection([
            'city' => 'Município de Porto Alegre',
            'state' => 'Rio Grande do Sul',
        ]);
        $this->assertEquals(
            (object) [
                'name' => 'Aeroporto Internacional de Porto Alegre',
                'iata' => 'POA',
                'icao' => 'SBPA',
                'city' => 'Município de Porto Alegre',
                'country' => 'Brazil',
                'state' => 'Rio Grande do Sul',
            ],
            $this->object->merge($a, $b)->all(),
        );
    }

    /**
     * Test if can cast values as arrays.
     */
    public function testOutputsArrays(): void
    {
        $result = $this->associative->toArray();
        $this->assertEquals($this->sources['associative'], $result);
        $result = $this->list->toArray();
        $this->assertEquals($this->sources['list'], $result);
        $result = $this->object->toArray();
        $this->assertEquals((array) $this->sources['object'], $result);
    }

    /**
     * Test if can cast values as objects.
     */
    public function testOutputsObject(): void
    {
        $result = $this->associative->toObject();
        $this->assertEquals((object) $this->sources['associative'], $result);
        $result = $this->list->toObject();
        $this->assertEquals((object) $this->sources['list'], $result);
        $result = $this->object->toObject();
        $this->assertEquals($this->sources['object'], $result);
    }

    /**
     * Test if can set and unset entire columns.
     */
    public function testSetsAndUnsetsColumns(): void
    {
        $this->assertEquals(
            [
                (object) [
                    'call_sign' => 'FBZ5902',
                    'origin' => 'AEP',
                    'destination' => 'POA',
                ],
                (object) [
                    'call_sign' => 'TAM3476',
                    'origin' => 'CWB',
                    'destination' => 'POA',
                ],
                (object) [
                    'call_sign' => 'AZU8725',
                    'origin' => 'MVD',
                    'destination' => 'POA',
                ],
                (object) [
                    'call_sign' => 'ARG1152',
                    'origin' => 'GRU',
                    'destination' => 'POA',
                ],
                (object) [
                    'call_sign' => 'TAM3322',
                    'origin' => 'GRU',
                    'destination' => 'POA',
                ],
            ],
            $this->list
                ->assign('destination', 'POA')
                ->drop('id')
                ->all(),
        );
        $array_list = new Collection([
            ['name' => 'John', 'city' => 'Itu'],
            ['name' => 'Mary', 'city' => 'Canoas'],
            ['name' => 'Jane', 'city' => 'Macapá'],
        ]);
        $this->assertEquals(
            [
                ['name' => 'John', 'country' => 'BRA'],
                ['name' => 'Mary', 'country' => 'BRA'],
                ['name' => 'Jane', 'country' => 'BRA'],
            ],
            $array_list
                ->assign('country', 'BRA')
                ->drop('city')
                ->all(),
        );
    }

    /**
     * Test if can set and unset values.
     */
    public function testSetsAndUnsetsValues(): void
    {
        $this->associative
            ->set('model', '737-400')
            ->set('manufacturer', 'Boeing')
            ->unset('introduction')
            ->unset('crew');
        $this->assertEquals(
            [
                'model' => '737-400',
                'manufacturer' => 'Boeing',
            ],
            $this->associative->all(),
        );
        $this->object
            ->unset('name')
            ->unset('city')
            ->set('iata', 'ABC')
            ->set('icao', 'WXYZ');
        $this->assertEquals(
            (object) [
                'iata' => 'ABC',
                'icao' => 'WXYZ',
            ],
            $this->object->all(),
        );
    }

    /**
     * Test if can create collections with a slice of the previous one.
     */
    public function testSlicesValues(): void
    {
        $this->assertSame(
            [
                'manufacturer' => 'Airbus',
                'crew' => 2,
            ],
            $this->associative->slice(1, 2)->all(),
        );
        $this->assertSame(
            [
                'manufacturer' => 'Airbus',
                'crew' => 2,
            ],
            $this->associative->slice(-3, -1)->all(),
        );
        $this->assertEquals(
            [
                2 => (object) [
                    'id' => 3,
                    'call_sign' => 'AZU8725',
                    'origin' => 'MVD',
                    'destination' => 'CWB',
                ],
                3 => (object) [
                    'id' => 4,
                    'call_sign' => 'ARG1152',
                    'origin' => 'GRU',
                    'destination' => 'AEP',
                ],
                4 => (object) [
                    'id' => 5,
                    'call_sign' => 'TAM3322',
                    'origin' => 'GRU',
                    'destination' => 'CXJ',
                ],
            ],
            $this->list->slice(-3)->all(),
        );
        $this->assertEquals([], $this->list->slice(8, -2)->all());
        $this->assertEquals([], $this->list->slice(0, -8)->all());
        $this->assertEquals([], $this->list->slice(-8, -8)->all());
    }

    /**
     * Test if can sort values from stored array.
     */
    public function testSortsArrays(): void
    {
        $this->associative->sort();
        $this->assertEquals(
            ['crew', 'introduction', 'model', 'manufacturer'],
            $this->associative->keys()->all(),
        );
        $this->assertEquals(
            [2, 1988, 'A320', 'Airbus'],
            $this->associative->values()->all(),
        );
        $this->associative->sort(function ($a, $b) {
            return strlen(strval($b)) <=> strlen(strval($a));
        });
        $this->assertEquals(
            ['manufacturer', 'introduction', 'model', 'crew'],
            $this->associative->keys()->all(),
        );
        $this->assertEquals(
            ['Airbus', 1988, 'A320', 2],
            $this->associative->values()->all(),
        );
    }

    /**
     * Test if can sort values from stored array.
     */
    public function testSortsObjects(): void
    {
        $this->object->sort();
        $this->assertEquals(
            ['name', 'iata', 'city', 'icao'],
            $this->object->keys()->all(),
        );
        $this->assertEquals(
            [
                'Aeroporto Internacional Salgado Filho',
                'POA',
                'Porto Alegre',
                'SBPA',
            ],
            $this->object->values()->all(),
        );
        $this->object->sort(function ($a, $b) {
            return strlen(strval($a)) <=> strlen(strval($b));
        });
        $this->assertEquals(
            ['iata', 'icao', 'city', 'name'],
            $this->object->keys()->all(),
        );
        $this->assertEquals(
            [
                'POA',
                'SBPA',
                'Porto Alegre',
                'Aeroporto Internacional Salgado Filho',
            ],
            $this->object->values()->all(),
        );
    }

    /**
     * Test if can unset collection keys.
     */
    public function testUnsetsKeys(): void
    {
        $this->associative
            ->unset('introduction')
            ->unset('manufacturer')
            ->unset('model');
        $this->assertEquals(['crew' => 2], $this->associative->all());
        $this->object
            ->unset('name')
            ->unset('city');
        $this->assertEquals(
            (object) [
                'iata' => 'POA',
                'icao' => 'SBPA',
            ],
            $this->object->all(),
        );
    }

    /**
     * This method is called before each test.
     */
    protected function setUp(): void
    {
        $this->sources['associative'] = [
            'model' => 'A320',
            'manufacturer' => 'Airbus',
            'crew' => 2,
            'introduction' => 1988,
        ];
        $this->sources['list'] = [
            (object) [
                'id' => 1,
                'call_sign' => 'FBZ5902',
                'origin' => 'AEP',
                'destination' => 'GIG',
            ],
            (object) [
                'id' => 2,
                'call_sign' => 'TAM3476',
                'origin' => 'CWB',
                'destination' => 'POA',
            ],
            (object) [
                'id' => 3,
                'call_sign' => 'AZU8725',
                'origin' => 'MVD',
                'destination' => 'CWB',
            ],
            (object) [
                'id' => 4,
                'call_sign' => 'ARG1152',
                'origin' => 'GRU',
                'destination' => 'AEP',
            ],
            (object) [
                'id' => 5,
                'call_sign' => 'TAM3322',
                'origin' => 'GRU',
                'destination' => 'CXJ',
            ],
        ];
        $this->sources['object'] = (object) [
            'name' => 'Aeroporto Internacional Salgado Filho',
            'iata' => 'POA',
            'icao' => 'SBPA',
            'city' => 'Porto Alegre',
        ];
        $this->associative = new Collection($this->sources['associative']);
        $this->list = new Collection($this->sources['list']);
        $this->object = new Collection($this->sources['object']);
    }
}
