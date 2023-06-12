<?php

use KSamuel\FacetedSearch\Filter\ExcludeValueFilter;
use PHPUnit\Framework\TestCase;
use KSamuel\FacetedSearch\Filter\RangeFilter;
use KSamuel\FacetedSearch\Index\Factory;
use KSamuel\FacetedSearch\Index\IndexInterface;
use KSamuel\FacetedSearch\Indexer\Number\RangeIndexer;
use KSamuel\FacetedSearch\Query\Order;
use KSamuel\FacetedSearch\Query\SearchQuery;
use KSamuel\FacetedSearch\Search;

class RangeFilterTest extends TestCase
{
    public function testSetWrongValueException(): void
    {
        $filter = new RangeFilter('field');
        $this->expectException('Exception');
        $filter->setValue(1);
    }

    public function testSetEmptyValueException(): void
    {
        $filter = new RangeFilter('field');
        $this->expectException('Exception');
        $filter->setValue(['min' => null, 'max' => null]);
    }

    public function testSetValue(): void
    {
        $filter = new RangeFilter('field');
        $filter->setValue(['min' => 10]);

        $this->assertEquals(['min' => 10, 'max' => null], $filter->getValue());
    }

    public function testCombinationTest(): void
    {
        $index = (new Factory)->create(Factory::ARRAY_STORAGE);
        $storage = $index->getStorage();

        $rangeIndexer = new RangeIndexer(100);
        $storage->addIndexer('price', $rangeIndexer);

        $storage->addRecord(1, ['price' => 90]);
        $storage->addRecord(2, ['price' => 100]);
        $storage->addRecord(3, ['price' => 150]);
        $storage->addRecord(4, ['price' => 200]);

        $filters = [
            new RangeFilter('price', ['min' => 100, 'max' => 200])
        ];

        $result = $index->query((new SearchQuery)->filters($filters));
        $this->assertEquals([2, 3, 4], $result);
    }

    public function testRangeWithExclude(): void
    {
        $this->rangeAndExclude((new Factory)->create(Factory::ARRAY_STORAGE));
        $this->rangeAndExclude((new Factory)->create(Factory::FIXED_ARRAY_STORAGE));
    }

    private function rangeAndExclude(IndexInterface $index): void
    {
        $storage = $index->getStorage();

        $rangeIndexer = new RangeIndexer(100);
        $storage->addIndexer('price', $rangeIndexer);

        $storage->addRecord(1, ['price' => 90, 'color' => ['black', 'red']]);
        $storage->addRecord(2, ['price' => 100, 'color' => ['black', 'red']]);
        $storage->addRecord(3, ['price' => 150, 'color' => ['black', 'red', 'green']]);
        $storage->addRecord(4, ['price' => 200, 'color' => ['black', 'red']]);

        $storage->optimize();

        $filters = [
            new RangeFilter('price', ['min' => 100, 'max' => 200]),
            new ExcludeValueFilter('color', ['green'])
        ];

        $result = $index->query((new SearchQuery)->filters($filters)->order('price', Order::SORT_ASC));
        $this->assertEquals([2, 4], $result);
    }
}
