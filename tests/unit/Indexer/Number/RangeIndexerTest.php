<?php
use PHPUnit\Framework\TestCase;
use KSamuel\FacetedSearch\Index;

class RangeIndexerTest extends TestCase
{
    public function testAddRecord()
    {
        $index = new Index();
        $indexer = new \KSamuel\FacetedSearch\Indexer\Number\RangeIndexer(100);
        $index->addIndexer('price', $indexer);

        $this->assertTrue($index->addRecord(2, ['price' => 90]));
        $this->assertTrue($index->addRecord(3, ['price' => 100]));
        $this->assertTrue($index->addRecord(4, ['price' => 110]));
        $this->assertTrue($index->addRecord(5, ['price' => 1000]));

        $this->assertEquals([
            'price' => [
                0 => [2],
                100 => [3 , 4],
                1000 => [5]
            ]
        ], $index->getData());
    }
}