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
        $index = new Index();
        $this->assertTrue($index->addRecord(112, ['vendor'=>'Tester','price' => 100]));
        $this->assertTrue($index->addRecord(113, ['vendor'=>'Tester2','price' => 101]));
        $this->assertTrue($index->addRecord(114, ['vendor'=>'Tester2','price' => 101]));
        ;
        $this->assertEquals([
            'vendor' => [
                'Tester' => [112=>true],
                'Tester2' => [113=>true,114=>true]
            ],
            'price' => [
                100 =>  [112 => true],
                101 => [113 =>true,114 =>true]
            ]
        ], $index->getData());
    }
}