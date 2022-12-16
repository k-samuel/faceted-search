<?php

declare(strict_types=1);

namespace KSamuel\FacetedSearch\Tests\Benchmark\Bench\ArrayIndex;

use KSamuel\FacetedSearch\Tests\Benchmark\ArrayIndexBench;

/**
 * @Iterations(5)
 * @Revs(10)
 * @BeforeMethods({"before"})
 */
class A_10kBench extends ArrayIndexBench
{
    /**
     * @var int
     */
    protected $dataSize = 10000;
}
