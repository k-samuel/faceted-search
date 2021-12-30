<?php

declare(strict_types=1);

namespace KSamuel\FacetedSearch\Tests\Benchmark;

use KSamuel\FacetedSearch\Filter\FilterInterface;
use KSamuel\FacetedSearch\Index;
use KSamuel\FacetedSearch\Search;
use KSamuel\FacetedSearch\Filter\ValueFilter;
use KSamuel\FacetedSearch\Sorter\ByField;
use KSamuel\FacetedSearch\Tests\Benchmark\DatasetFactory;
use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use PhpBench\Benchmark\Metadata\Annotations\Groups;
use PhpBench\Benchmark\Metadata\Annotations\Iterations;
use PhpBench\Benchmark\Metadata\Annotations\ParamProviders;
use PhpBench\Benchmark\Metadata\Annotations\Revs;

/**
 * @Iterations(5)
 * @Revs(10)
 * @BeforeMethods({"before"})
 */
class SearchBench
{
    /**
     * @var Index
     */
    private $index;
    /**
     * @var Search
     */
    private $search;
    /**
     * @var int
     */
    protected $dataSize = 1000000;
    /**
     * @var FilterInterface[]
     */
    private $filters;
    /**
     * @var array<int,int>
     */
    private $firstResults;
    /**
     * @var ByField
     */
    private $sorter;

    public function before(): void
    {
        $this->index = (new DatasetFactory('tests/data/'))->getFacetedIndex($this->dataSize);
        $this->search = new Search($this->index);
        $this->filters = [
            new ValueFilter('color', 'black'),
            new ValueFilter('warehouse', [789, 45, 65, 1, 10]),
            new ValueFilter('type', ["normal", "middle"])
        ];
        $this->firstResults = $this->search->find($this->filters);
        $this->sorter = new ByField($this->index);
    }

    public function benchFind(): void
    {
        $result = $this->search->find($this->filters);
    }

    public function benchAggregations(): void
    {
        $result = $this->search->findAcceptableFiltersCount($this->filters);
    }

    public function benchSort(): void
    {
        $result = $this->sorter->sort($this->firstResults, 'quantity');
    }
}