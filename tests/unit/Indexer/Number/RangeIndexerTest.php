<?php

use PHPUnit\Framework\TestCase;
use KSamuel\FacetedSearch\Index\Factory;
use KSamuel\FacetedSearch\Indexer\Number\RangeIndexer;

class RangeIndexerTest extends TestCase
{
    public function testAddRecord(): void
    {
        $index = (new Factory)->create(Factory::ARRAY_STORAGE);
        $storage = $index->getStorage();
        $indexer = new RangeIndexer(100);
        $storage->addIndexer('price', $indexer);

        $this->assertTrue($storage->addRecord(2, ['price' => 90]));
        $this->assertTrue($storage->addRecord(3, ['price' => 100]));
        $this->assertTrue($storage->addRecord(4, ['price' => 110]));
        $this->assertTrue($storage->addRecord(5, ['price' => 1000]));

        $this->assertEquals([
            'price' => [
                0 => [2],
                100 => [3, 4],
                1000 => [5],
            ],
        ], $storage->getData());
    }

    public function testFixedAddRecord(): void
    {
        $index = (new Factory)->create(Factory::FIXED_ARRAY_STORAGE);
        $storage = $index->getStorage();
        $indexer = new RangeIndexer(100);
        $storage->addIndexer('price', $indexer);

        $this->assertTrue($storage->addRecord(2, ['price' => 90]));
        $this->assertTrue($storage->addRecord(3, ['price' => 100]));
        $this->assertTrue($storage->addRecord(4, ['price' => 110]));
        $this->assertTrue($storage->addRecord(5, ['price' => 1000]));

        $this->assertEquals([
            'price' => [
                0 => [2],
                100 => [3, 4],
                1000 => [5],
            ]
        ], $storage->export());
    }
}
