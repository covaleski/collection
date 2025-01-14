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
     * Collection instance containing an associative array.
     */
    public Collection $assoc;

    /**
     * Collection instance containing a list array.
     */
    public Collection $list;

    /**
     * Collection instance containing an object.
     */
    public Collection $object;

    /**
     * Test if can concatenate associative array collections.
     */
    public function testConcatenatesAssociativeArrays(): void
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
            $this->assoc->merge($a, $b)->toArray(),
        );
    }

    /**
     * Test if can concatenate list collections.
     */
    public function testConcatenatesLists(): void
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
            $this->list->merge($collection)->toArray(),
        );
    }

    /**
     * Test if can concatenate object collections.
     */
    public function testConcatenatesObjects(): void
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
            [
                'name' => 'Aeroporto Internacional de Porto Alegre',
                'iata' => 'POA',
                'icao' => 'SBPA',
                'city' => 'Município de Porto Alegre',
                'country' => 'Brazil',
                'state' => 'Rio Grande do Sul',
            ],
            $this->object->merge($a, $b)->toArray(),
        );
    }

    /**
     * Test if can filter values into a new collection.
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
                ->toArray(),
        );
    }

    /**
     * Test if can capture a column of values into a new collection.
     */
    public function testGetsColumns(): void
    {
        $this->assertEquals(
            ['FBZ5902', 'TAM3476', 'AZU8725', 'ARG1152', 'TAM3322'],
            $this->list->column('call_sign')->toArray(),
        );
    }

    /**
     * Test if can get the collection keys.
     */
    public function testGetsKeys(): void
    {
        $this->assertEquals(
            ['model', 'manufacturer', 'crew', 'introduction'],
            $this->assoc->keys()->toArray(),
        );
        $this->assertEquals(
            [0, 1, 2, 3, 4],
            $this->list->keys()->toArray(),
        );
        $this->assertEquals(
            ['name', 'iata', 'icao', 'city'],
            $this->object->keys()->toArray(),
        );
    }

    /**
     * Test if can get the collection values.
     */
    public function testGetsValues(): void
    {
        $this->assertEquals(
            ['A320', 'Airbus', 2, 1988],
            $this->assoc->values()->toArray(),
        );
        $this->assertEquals(
            [
                'Aeroporto Internacional Salgado Filho',
                'POA',
                'SBPA',
                'Porto Alegre',
            ],
            $this->object->values()->toArray(),
        );
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
     * Test if can capture the results of a callback into a new collection.
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
                ->toArray(),
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
                ->toArray(),
        );
    }

    /**
     * This method is called before each test.
     */
    protected function setUp(): void
    {
        $this->assoc = new Collection([
            'model' => 'A320',
            'manufacturer' => 'Airbus',
            'crew' => 2,
            'introduction' => 1988,
        ]);
        $this->object = new Collection((object) [
            'name' => 'Aeroporto Internacional Salgado Filho',
            'iata' => 'POA',
            'icao' => 'SBPA',
            'city' => 'Porto Alegre',
        ]);
        $this->list = new Collection([
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
        ]);
    }
}
