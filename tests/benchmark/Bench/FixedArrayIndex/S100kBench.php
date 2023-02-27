<?php

declare(strict_types=1);

namespace KSamuel\FacetedSearch\Tests\Benchmark\Bench\FixedArrayIndex;

use KSamuel\FacetedSearch\Tests\Benchmark\FixedArrayIndexBench;


/**
 * @Iterations(5)
 * @Revs(10)
 * @BeforeMethods({"before"})
 */
class S100kBench extends FixedArrayIndexBench
{
    /**
     * @var int
     */
    protected int $dataSize = 100000;
}
