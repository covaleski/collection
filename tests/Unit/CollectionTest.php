<?php

declare(strict_types=1);

namespace Tests\Unit\Collection;

use Covaleski\Collection\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
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
        $this->assertSame(
            'Aeroporto Internacional Salgado Filho',
            $this->object->get('name'),
        );
        $this->assertSame('POA', $this->object->get('iata'));
        $this->assertSame('SBPA', $this->object->get('icao'));
        $this->assertSame('Porto Alegre', $this->object->get('city'));
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
        $this->assertSame('A320', $this->associative->nth(-4));
        $this->assertSame('Airbus', $this->associative->nth(-3));
        $this->assertSame(2, $this->associative->nth(-2));
        $this->assertSame(1988, $this->associative->nth(-1));
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
     * Test if can capture all values in the specified list "column".
     */
    public function testGetsColumns(): void
    {
        $this->assertEquals(
            ['FBZ5902', 'TAM3476', 'AZU8725', 'ARG1152', 'TAM3322'],
            $this->list->column('call_sign')->all(),
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
