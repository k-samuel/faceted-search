<?php

use PHPUnit\Framework\TestCase;
use KSamuel\FacetedSearch\Search;
use KSamuel\FacetedSearch\Filter\ValueFilter;
use KSamuel\FacetedSearch\Filter\RangeFilter;
use KSamuel\FacetedSearch\Index;

class IndexTest extends TestCase
{
    public function testAddRecord(): void
    {
        $index = new Index\ArrayIndex();
        $this->assertTrue($index->addRecord(112, ['vendor' => 'Tester', 'price' => 100]));
        $this->assertTrue($index->addRecord(113, ['vendor' => 'Tester2', 'price' => 101]));
        $this->assertTrue($index->addRecord(114, ['vendor' => 'Tester2', 'price' => 101]));
        $this->assertEquals([
            'vendor' => [
                'Tester' => [112],
                'Tester2' => [113, 114]
            ],
            'price' => [
                100 => [112],
                101 => [113, 114]
            ]
        ], $index->getData());
        $this->assertTrue($index->addRecord(114, ['vendor' => 'Tester2', 'price' => 0.15]));
    }

    public function testFixedAddRecord(): void
    {
        $index = new Index\FixedArrayIndex();
        $this->assertTrue($index->addRecord(112, ['vendor' => 'Tester', 'price' => 100]));
        $this->assertTrue($index->addRecord(113, ['vendor' => 'Tester2', 'price' => 101]));
        $this->assertTrue($index->addRecord(114, ['vendor' => 'Tester2', 'price' => 101]));
        $this->assertEquals([
            'vendor' => [
                'Tester' => [112],
                'Tester2' => [113, 114]
            ],
            'price' => [
                100 => [112],
                101 => [113, 114]
            ]
        ], $index->export());
        $this->assertTrue($index->addRecord(114, ['vendor' => 'Tester2', 'price' => 0.15]));
    }

    public function testGetAllRecordId(): void
    {
        $index = new Index\ArrayIndex();
        $this->assertTrue($index->addRecord(112, ['vendor' => 'Tester', 'price' => 100]));
        $this->assertTrue($index->addRecord(113, ['vendor' => 'Tester2', 'price' => 101]));
        $this->assertTrue($index->addRecord(114, ['vendor' => 'Tester2', 'price' => 101]));

        $this->assertEquals([112, 113, 114,], $index->getAllRecordId());
        $this->assertEquals([112 => true, 113 => true, 114 => true], $index->getAllRecordIdMap());
        $this->assertEquals(2, $index->getRecordsCount('price', 101));
        $this->assertEquals(2, $index->getRecordsCount('price', 101));
    }

    public function testRecordsCount(): void
    {
        $index = new Index\ArrayIndex();
        $this->assertTrue($index->addRecord(112, ['vendor' => 'Tester', 'price' => 100]));
        $this->assertTrue($index->addRecord(113, ['vendor' => 'Tester2', 'price' => 101]));
        $this->assertTrue($index->addRecord(114, ['vendor' => 'Tester2', 'price' => 101]));

        $this->assertEquals(2, $index->getRecordsCount('price', 101));
        $this->assertEquals(0, $index->getRecordsCount('price', 500));
        $this->assertEquals(0, $index->getRecordsCount('price2', 500));
    }

    public function testHasField(): void
    {
        $index = new Index\ArrayIndex();
        $this->assertTrue($index->addRecord(112, ['vendor' => 'Tester', 'price' => 100]));
        $this->assertTrue($index->addRecord(113, ['vendor' => 'Tester2', 'price' => 101]));
        $this->assertTrue($index->addRecord(114, ['vendor' => 'Tester2', 'price' => 101]));

        $this->assertTrue($index->hasField('vendor'));
        $this->assertFalse($index->hasField('undefined_field'));
    }

    public function testAggregate(): void
    {
        $index = new Index\ArrayIndex();
        $this->assertTrue($index->addRecord(112, ['vendor' => 'Tester', 'price' => 100]));
        $this->assertTrue($index->addRecord(113, ['vendor' => 'Tester2', 'price' => 101]));
        $this->assertTrue($index->addRecord(114, ['vendor' => 'Tester2', 'price' => 101]));

        $this->assertEquals(
            [
                'vendor' => [
                    'Tester' => 1,
                    'Tester2' => 2
                ],
                'price' => [
                    100 => 1,
                    101 => 2
                ]
            ],
            $index->aggregate([], [], true)
        );
    }

    public function testExport(): void
    {
        $index = new Index\FixedArrayIndex();
        $index->writeMode();
        $this->assertTrue($index->addRecord(112, ['vendor' => 'Tester', 'price' => 100]));
        $index->commitChanges();
        $index->writeMode();
        $this->assertTrue($index->addRecord(113, ['vendor' => 'Tester2', 'price' => 101]));

        $this->assertEquals(
            [
                'vendor' => [
                    'Tester' => [112],
                    'Tester2' => [113],
                ],
                'price' => [
                    100 => [112],
                    101 => [113]
                ]
            ],
            $index->export()
        );
    }
}
