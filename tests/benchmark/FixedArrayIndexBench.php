<?php

declare(strict_types=1);

namespace KSamuel\FacetedSearch\Tests\Benchmark;

use KSamuel\FacetedSearch\Search;
use KSamuel\FacetedSearch\Filter\ValueFilter;
use KSamuel\FacetedSearch\Sorter\ByField;
use KSamuel\FacetedSearch\Tests\Benchmark\DatasetFactory;
use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use PhpBench\Benchmark\Metadata\Annotations\Iterations;
use PhpBench\Benchmark\Metadata\Annotations\Revs;
use KSamuel\FacetedSearch\Query\AggregationQuery;
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
        $index = (new DatasetFactory('tests/data/'))->getFixedFacetedIndex($this->dataSize, $this->isBalanced);
        $this->search = new Search($index);
        $this->filters = [
            new ValueFilter('color', 'black'),
            new ValueFilter('warehouse', [789, 45, 65, 1, 10]),
            new ValueFilter('type', ["normal", "middle"])
        ];
        $this->searchQuery = (new SearchQuery())->filters($this->filters);
        $this->aggregationQuery = (new AggregationQuery())->filters($this->filters);
        $this->aggregationQueryCount = (new AggregationQuery())->filters($this->filters)->countItems();
        $this->firstResults = $this->search->query($this->searchQuery);
    }
}
