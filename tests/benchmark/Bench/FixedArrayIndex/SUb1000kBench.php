<?php

declare(strict_types=1);

namespace KSamuel\FacetedSearch\Tests\Benchmark\Bench\ArrayIndex;

use KSamuel\FacetedSearch\Tests\Benchmark\FixedArrayIndexBench;

/**
 * @Iterations(5)
 * @Revs(10)
 * @BeforeMethods({"before"})
 */
class UB_1000kBench extends FixedArrayIndexBench
{
    protected bool $isBalanced = false;
    /**
     * @var int
     */
    protected int $dataSize = 1000000;
}
