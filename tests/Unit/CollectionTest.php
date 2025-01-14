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
     * Collection instance containing a list array.
     */
    public Collection $list;

    /**
     * Collection instance containing an object.
     */
    public Collection $object;

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
