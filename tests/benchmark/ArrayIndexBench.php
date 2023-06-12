<?php

declare(strict_types=1);

namespace KSamuel\FacetedSearch\Tests\Benchmark;

use KSamuel\FacetedSearch\Filter\ExcludeValueFilter;
use KSamuel\FacetedSearch\Filter\FilterInterface;

use KSamuel\FacetedSearch\Filter\ValueFilter;
use KSamuel\FacetedSearch\Index\IndexInterface;
use KSamuel\FacetedSearch\Query\AggregationQuery;
use KSamuel\FacetedSearch\Query\Order;
use KSamuel\FacetedSearch\Query\SearchQuery;

use KSamuel\FacetedSearch\Tests\Benchmark\DatasetFactory;
use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use PhpBench\Benchmark\Metadata\Annotations\Iterations;
use PhpBench\Benchmark\Metadata\Annotations\Revs;

/**
 * @Iterations(5)
 * @Revs(10)
 * @BeforeMethods({"before"})
 */
class ArrayIndexBench
{

    protected IndexInterface $index;

    protected int $dataSize = 1000000;
    /**
     * @var array<FilterInterface> $filters
     */
    protected array $filters;
    /**
     * @var array<FilterInterface> $excludeFilters
     */
    protected array $excludeFilters;
    /**
     * @var array<int,int>
     */
    protected $firstResults;

    protected SearchQuery $searchQuery;
    protected SearchQuery $searchQuerySorted;
    protected SearchQuery $searchQueryExclude;
    protected AggregationQuery $aggregationQuery;
    protected AggregationQuery $aggregationQueryCount;
    protected AggregationQuery $aggregationExcludeQueryCount;

    protected bool $isBalanced = true;

    public function before(): void
    {
        $this->index = (new DatasetFactory('tests/data/'))->getFacetedIndex($this->dataSize, $this->isBalanced);
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

    public function benchFind(): void
    {
        $result = $this->index->query($this->searchQuery);
    }

    public function benchFindAndSort(): void
    {
        $result = $this->index->query($this->searchQuerySorted);
    }

    public function benchAggregations(): void
    {
        $result = $this->index->aggregate($this->aggregationQuery);
    }

    public function benchAggregationsAndCount(): void
    {
        $result = $this->index->aggregate($this->aggregationQueryCount);
    }

    public function benchQueryExcludeFilters(): void
    {
        $result = $this->index->query($this->searchQueryExclude);
    }

    public function benchAggregateExcludeFilters(): void
    {
        $result = $this->index->aggregate($this->aggregationExcludeQueryCount);
    }
}
