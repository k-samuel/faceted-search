<?php

declare(strict_types=1);

namespace KSamuel\FacetedSearch\Tests\Benchmark\Bench\ArrayIndex;

use KSamuel\FacetedSearch\Tests\Benchmark\ArrayIndexBench;

/**
 * @Iterations(5)
 * @Revs(10)
 * @BeforeMethods({"before"})
 */
class AUb1000kBench extends ArrayIndexBench
{
    protected bool $isBalanced = false;
    /**
     * @var int
     */
    protected int $dataSize = 1000000;
}
