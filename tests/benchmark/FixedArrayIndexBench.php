<?php

declare(strict_types=1);

namespace KSamuel\FacetedSearch\Tests\Benchmark;

use KSamuel\FacetedSearch\Filter\ExcludeValueFilter;
use KSamuel\FacetedSearch\Search;
use KSamuel\FacetedSearch\Filter\ValueFilter;
use KSamuel\FacetedSearch\Index\Factory;
use KSamuel\FacetedSearch\Sorter\ByField;
use KSamuel\FacetedSearch\Tests\Benchmark\DatasetFactory;
use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use PhpBench\Benchmark\Metadata\Annotations\Iterations;
use PhpBench\Benchmark\Metadata\Annotations\Revs;
use KSamuel\FacetedSearch\Query\AggregationQuery;
use KSamuel\FacetedSearch\Query\Order;
use KSamuel\FacetedSearch\Query\SearchQuery;

/**
 * @Iterations(5)
 * @Revs(10)
 * @BeforeMethods({"before"})
 */
class FixedArrayIndexBench extends ArrayIndexBench
{
    public function before(): void
    {
        $this->index = (new DatasetFactory('tests/data/'))->getFixedFacetedIndex($this->dataSize, $this->isBalanced);

        $this->filters = [
            new ValueFilter('color', 'black'),
            new ValueFilter('warehouse', [789, 45, 65, 1, 10]),
            new ValueFilter('type', ["normal", "middle"])
        ];

        $this->excludeFilters = [
            new ValueFilter('color', 'black'),
            new ValueFilter('warehouse', [789, 45, 65, 1, 10]),
            new ExcludeValueFilter('type', ['good'])
        ];

        $this->searchQuery = (new SearchQuery())->filters($this->filters);
        $this->searchQuerySorted = clone $this->searchQuery;
        $this->searchQuerySorted->order('quantity', Order::SORT_DESC);
        $this->searchQueryExclude = (new SearchQuery())->filters($this->excludeFilters);
        $this->aggregationQuery = (new AggregationQuery())->filters($this->filters);
        $this->aggregationQueryCount = (new AggregationQuery())->filters($this->filters)->countItems();
        $this->aggregationExcludeQueryCount = (new AggregationQuery())->filters($this->excludeFilters)->countItems();

        $this->firstResults = $this->index->query($this->searchQuery);
    }
}
