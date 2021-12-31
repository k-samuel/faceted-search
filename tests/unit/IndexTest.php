<?php
use PHPUnit\Framework\TestCase;
use KSamuel\FacetedSearch\Search;
use KSamuel\FacetedSearch\Filter\ValueFilter;
use KSamuel\FacetedSearch\Filter\RangeFilter;
use KSamuel\FacetedSearch\Index;

class IndexTest extends TestCase
{
    public function testAddRecord()
    {
        $index = new Index\ArrayIndex();
        $this->assertTrue($index->addRecord(112, ['vendor'=>'Tester','price' => 100]));
        $this->assertTrue($index->addRecord(113, ['vendor'=>'Tester2','price' => 101]));
        $this->assertTrue($index->addRecord(114, ['vendor'=>'Tester2','price' => 101]));
        $this->assertEquals([
            'vendor' => [
                'Tester' => [112],
                'Tester2' => [113,114]
            ],
            'price' => [
                100 =>  [112],
                101 => [113,114]
            ]
        ], $index->getData());
        $this->assertTrue($index->addRecord(114, ['vendor'=>'Tester2','price' => 0.15]));
    }

    public function testFixedAddRecord()
    {
        $index = new Index\FixedArrayIndex();
        $this->assertTrue($index->addRecord(112, ['vendor'=>'Tester','price' => 100]));
        $this->assertTrue($index->addRecord(113, ['vendor'=>'Tester2','price' => 101]));
        $this->assertTrue($index->addRecord(114, ['vendor'=>'Tester2','price' => 101]));
        $this->assertEquals([
            'vendor' => [
                'Tester' => [112],
                'Tester2' => [113,114]
            ],
            'price' => [
                100 =>  [112],
                101 => [113,114]
            ]
        ], $index->export());
        $this->assertTrue($index->addRecord(114, ['vendor'=>'Tester2','price' => 0.15]));
    }
}