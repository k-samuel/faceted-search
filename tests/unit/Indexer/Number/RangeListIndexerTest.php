<?php
use PHPUnit\Framework\TestCase;
use KSamuel\FacetedSearch\Index;
use KSamuel\FacetedSearch\Indexer\Number\RangeListIndexer;

class RangeListIndexerTest extends TestCase
{
    public function testAddRecord()
    {
        $index = new Index();
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
                0 => [2 => true],
                100 => [3 => true ,4 => true],
                500 => [5 =>true]
            ]
        ], $index->getData());
    }
}