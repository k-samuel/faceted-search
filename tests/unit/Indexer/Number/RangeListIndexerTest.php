<?php
use PHPUnit\Framework\TestCase;
use KSamuel\FacetedSearch\Index;
use KSamuel\FacetedSearch\Indexer\Number\RangeListIndexer;

class RangeListIndexerTest extends TestCase
{
    public function testAddRecord()
    {
        $index = new Index\ArrayIndex();
        $indexer = new RangeListIndexer([
            100,200,150,500
        ]);
        $index->addIndexer('price', $indexer);

        $this->assertTrue($index->addRecord(2, ['price' => 90]));
        $this->assertTrue($index->addRecord(3, ['price' => 100]));
        $this->assertTrue($index->addRecord(4, ['price' => 110]));
        $this->assertTrue($index->addRecord(5, ['price' => 1000]));

        $this->assertEquals([
            'price' => [
                0 => [2],
                100 => [3,4],
                500 => [5]
            ]
        ], $index->getData());
    }

    public function testFixedAddRecord()
    {
        $index = new Index\FixedArrayIndex();
        $indexer = new RangeListIndexer([100,200,150,500]);
        $index->addIndexer('price', $indexer);

        $this->assertTrue($index->addRecord(2, ['price' => 90]));
        $this->assertTrue($index->addRecord(3, ['price' => 100]));
        $this->assertTrue($index->addRecord(4, ['price' => 110]));
        $this->assertTrue($index->addRecord(5, ['price' => 1000]));

        $this->assertEquals([
            'price' => [
                0 => [2],
                100 => [3,4],
                500 => [5]
            ]
        ], $index->getData());
    }
}