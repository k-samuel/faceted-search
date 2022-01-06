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
class FixedArrayIndexBench extends ArrayIndexBench
{
    protected $sorter;

    public function before(): void
    {
        $index = (new DatasetFactory('tests/data/'))->getFixedFacetedIndex($this->dataSize);
        $this->search = new Search($index);
        $this->filters = [
            new ValueFilter('color', 'black'),
            new ValueFilter('warehouse', [789, 45, 65, 1, 10]),
            new ValueFilter('type', ["normal", "middle"])
        ];
        $this->firstResults = $this->search->find($this->filters);
        $this->sorter = new ByField($index);
    }
}