<?php

use KSamuel\FacetedSearch\Filter\ValueFilter;
use PHPUnit\Framework\TestCase;

use KSamuel\FacetedSearch\Index\Factory;
use KSamuel\FacetedSearch\Index\IndexInterface;
use KSamuel\FacetedSearch\Indexer\Number\RangeIndexer;
use KSamuel\FacetedSearch\Indexer\Number\RangeListIndexer;
use KSamuel\FacetedSearch\Query\Order;
use KSamuel\FacetedSearch\Query\SearchQuery;

class ResultTest extends TestCase
{

    public function testSortRange(): void
    {
        $this->sortTest((new Factory)->create(Factory::ARRAY_STORAGE));
        $this->sortTest((new Factory)->create(Factory::FIXED_ARRAY_STORAGE));
    }

    public function testSortRangeList(): void
    {
        $this->sortRangeListTest((new Factory)->create(Factory::ARRAY_STORAGE));
        $this->sortRangeListTest((new Factory)->create(Factory::FIXED_ARRAY_STORAGE));
    }

    private function sortTest(IndexInterface $index): void
    {
        $storage = $index->getStorage();
        $rangeIndexer = new RangeIndexer(100);
        $storage->addIndexer('price', $rangeIndexer);

        $storage->addRecord(1, ['price' => 50]);
        $storage->addRecord(2, ['price' => 107]);
        $storage->addRecord(3, ['price' => 103]);
        $storage->addRecord(4, ['price' => 112]);
        $storage->addRecord(5, ['price' => 210]);

        $storage->optimize();

        $filters = [
            new ValueFilter('price', 100)
        ];

        $query = new SearchQuery();
        $query->filters($filters)->order('price', Order::SORT_ASC);
        $result = $index->query($query);
        $this->assertEquals([3, 2, 4], $result);

        $query = new SearchQuery();
        $query->filters($filters)->order('price', Order::SORT_DESC);
        $result = $index->query($query);
        $this->assertEquals([4, 2, 3], $result);
    }

    private function sortRangeListTest(IndexInterface $index): void
    {
        $storage = $index->getStorage();
        $rangeIndexer = new RangeListIndexer([0, 100, 200]);
        $storage->addIndexer('price', $rangeIndexer);

        $storage->addRecord(1, ['price' => 50]);
        $storage->addRecord(2, ['price' => 107]);
        $storage->addRecord(3, ['price' => 103]);
        $storage->addRecord(4, ['price' => 112]);
        $storage->addRecord(5, ['price' => 210]);

        $storage->optimize();

        $filters = [
            new ValueFilter('price', 100)
        ];

        $query = new SearchQuery();
        $query->filters($filters)->order('price', Order::SORT_ASC);
        $result = $index->query($query);
        $this->assertEquals([3, 2, 4], $result);

        $query = new SearchQuery();
        $query->filters($filters)->order('price', Order::SORT_DESC);
        $result = $index->query($query);
        $this->assertEquals([4, 2, 3], $result);
    }
}
