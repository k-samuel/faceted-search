<?php
use PHPUnit\Framework\TestCase;
use KSamuel\FacetedSearch\Filter\RangeFilter;
use KSamuel\FacetedSearch\Index\ArrayIndex;
use KSamuel\FacetedSearch\Indexer\Number\RangeIndexer;
use KSamuel\FacetedSearch\Search;

class RangeFilterTest extends TestCase
{
    public function testSetWrongValueException()
    {
        $filter = new RangeFilter('field');
        $this->expectException('Exception');
        $filter->setValue(1);
    }

    public function testSetEmptyValueException()
    {
        $filter = new RangeFilter('field');
        $this->expectException('Exception');
        $filter->setValue(['min'=>null, 'max'=>null]);
    }

    public function testSetValue()
    {
        $filter = new RangeFilter('field');
        $filter->setValue(['min'=>10]);

        $this->assertEquals(['min'=>10,'max'=>null], $filter->getValue());
    }

    public function testCombinationTest()
    {
        $index = new ArrayIndex();
        $rangeIndexer = new RangeIndexer(100);
        $index->addIndexer('price', $rangeIndexer);

        $index->addRecord(1,['price'=>90]);
        $index->addRecord(2,['price'=>100]);
        $index->addRecord(3,['price'=>150]);
        $index->addRecord(4,['price'=>200]);

        $filters = [
            new RangeFilter('price', ['min'=>100,'max'=>200])
        ];

        $search = new Search($index);
        $result = $search->find($filters);
        $this->assertEquals([2,3,4], $result);
    }
}