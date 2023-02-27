<?php

use PHPUnit\Framework\TestCase;

use KSamuel\FacetedSearch\Index\Factory;


class ScannerTest extends TestCase
{
    public function testGetAllRecordId(): void
    {
        $index = (new Factory)->create(Factory::ARRAY_STORAGE);
        $storage = $index->getStorage();

        $this->assertTrue($storage->addRecord(112, ['vendor' => 'Tester', 'price' => 100]));
        $this->assertTrue($storage->addRecord(113, ['vendor' => 'Tester2', 'price' => 101]));
        $this->assertTrue($storage->addRecord(114, ['vendor' => 'Tester2', 'price' => 101]));

        $this->assertEquals([112 => true, 113 => true, 114 => true], $index->getScanner()->getAllRecordIdMap($index->getStorage()));
        $this->assertEquals(2, $storage->getRecordsCount('price', 101));
        $this->assertEquals(2, $storage->getRecordsCount('price', 101));
    }
}
