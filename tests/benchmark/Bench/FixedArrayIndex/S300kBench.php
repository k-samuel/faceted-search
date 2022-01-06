<?php

declare(strict_types=1);

namespace KSamuel\FacetedSearch\Tests\Benchmark\Bench\FixedArrayIndex;

use KSamuel\FacetedSearch\Filter\FilterInterface;
use KSamuel\FacetedSearch\Index;
use KSamuel\FacetedSearch\Search;
use KSamuel\FacetedSearch\Filter\ValueFilter;
use KSamuel\FacetedSearch\Tests\Benchmark\DatasetFactory;
use KSamuel\FacetedSearch\Tests\Benchmark\ArrayIndexBench;
use KSamuel\FacetedSearch\Tests\Benchmark\FixedArrayIndexBench;
use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use PhpBench\Benchmark\Metadata\Annotations\Iterations;
use PhpBench\Benchmark\Metadata\Annotations\Revs;

/**
 * @Iterations(5)
 * @Revs(10)
 * @BeforeMethods({"before"})
 */
class S300kBench extends FixedArrayIndexBench
{
    /**
     * @var int
     */
    protected $dataSize = 300000;
}