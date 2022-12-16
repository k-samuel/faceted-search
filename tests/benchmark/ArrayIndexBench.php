<?php

declare(strict_types=1);

namespace KSamuel\FacetedSearch\Tests\Benchmark;

use KSamuel\FacetedSearch\Filter\FilterInterface;
use KSamuel\FacetedSearch\Index;
use KSamuel\FacetedSearch\Search;
use KSamuel\FacetedSearch\Filter\ValueFilter;
use KSamuel\FacetedSearch\Query\AggregationQuery;
use KSamuel\FacetedSearch\Query\SearchQuery;
use KSamuel\FacetedSearch\Sorter\ByField;
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
    /**
     * @var Index
     */
    protected $index;
    /**
     * @var Search
     */
    protected $search;
    /**
     * @var int
     */
    protected $dataSize = 1000000;
    /**
     * @var FilterInterface[]
     */
    protected $filters;
    /**
     * @var array<int,int>
     */
    protected $firstResults;
    /**
     * @var ByField
     */
    protected $sorter;
    /**
     * @var SearchQuery
     */
    protected SearchQuery $searchQuery;
    /**
     * @var AggregationQuery
     */
    protected AggregationQuery $aggregationQuery;
    /**
     * @var AggregationQuery
     */
    protected AggregationQuery $aggregationQueryCount;

    protected bool $isBalanced = true;

    public function before(): void
    {
        $this->index = (new DatasetFactory('tests/data/'))->getFacetedIndex($this->dataSize, $this->isBalanced);
        $this->search = new Search($this->index);
        $this->filters = [
            new ValueFilter('color', 'black'),
            new ValueFilter('warehouse', [789, 45, 65, 1, 10]),
            new ValueFilter('type', ["normal", "middle"])
        ];
        $this->searchQuery = (new SearchQuery())->filters($this->filters);
        $this->aggregationQuery = (new AggregationQuery())->filters($this->filters);
        $this->aggregationQueryCount = (new AggregationQuery())->filters($this->filters)->countItems();
        $this->firstResults = $this->search->query($this->searchQuery);
        $this->sorter = new ByField($this->index);
    }

    public function benchFind(): void
    {
        $result = $this->search->query($this->searchQuery);
    }

    public function benchAggregations(): void
    {
        $result = $this->search->aggregate($this->aggregationQuery);
    }

    public function benchAggregationsAndCount(): void
    {
        $result = $this->search->aggregate($this->aggregationQueryCount);
    }

    public function benchSort(): void
    {
        $result = $this->sorter->sort($this->firstResults, 'quantity');
    }
}
