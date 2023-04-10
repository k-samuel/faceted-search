<?php

declare(strict_types=1);

namespace KSamuel\FacetedSearch\Tests\Benchmark;

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
     * @var array<int,int>
     */
    protected $firstResults;

    protected SearchQuery $searchQuery;
    protected SearchQuery $searchQuerySorted;
    protected AggregationQuery $aggregationQuery;
    protected AggregationQuery $aggregationQueryCount;

    protected bool $isBalanced = true;

    public function before(): void
    {
        $this->index = (new DatasetFactory('tests/data/'))->getFacetedIndex($this->dataSize, $this->isBalanced);
        $this->filters = [
            new ValueFilter('color', 'black'),
            new ValueFilter('warehouse', [789, 45, 65, 1, 10]),
            new ValueFilter('type', ["normal", "middle"])
        ];
        $this->searchQuery = (new SearchQuery())->filters($this->filters);
        $this->searchQuerySorted = clone $this->searchQuery;
        $this->searchQuerySorted->order('quantity', Order::SORT_DESC);

        $this->aggregationQuery = (new AggregationQuery())->filters($this->filters);
        $this->aggregationQueryCount = (new AggregationQuery())->filters($this->filters)->countItems();
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
}
