<?php

use PHPUnit\Framework\TestCase;
use KSamuel\FacetedSearch\Filter\ExcludeRangeFilter;
use KSamuel\FacetedSearch\Index\Factory;
use KSamuel\FacetedSearch\Indexer\Number\RangeIndexer;
use KSamuel\FacetedSearch\Query\SearchQuery;
use KSamuel\FacetedSearch\Search;

class ExcludeRangeFilterTest extends TestCase
{

    public function testCombinationTest(): void
    {
        $index = (new Factory)->create(Factory::ARRAY_STORAGE);
        $storage = $index->getStorage();

        $rangeIndexer = new RangeIndexer(50);
        $storage->addIndexer('price', $rangeIndexer);

        $storage->addRecord(1, ['price' => 90]);
        $storage->addRecord(2, ['price' => 110]);
        $storage->addRecord(3, ['price' => 140]);
        $storage->addRecord(4, ['price' => 200]);

        $storage->optimize();

        $filters = [
            new ExcludeRangeFilter('price', ['min' => 100, 'max' => 150])
        ];

        $result = $index->query((new SearchQuery)->filters($filters));
        $this->assertEquals([1, 4], $result);
    }
}
