<?php

use KSamuel\FacetedSearch\Query\AggregationSort;
use PHPUnit\Framework\TestCase;

class AggregationSortTest extends TestCase
{
    public function testCreate(): void
    {
        $sort = new AggregationSort(AggregationSort::SORT_ASC);
        $this->assertEquals(AggregationSort::SORT_ASC, $sort->getDirection());
        $this->assertEquals(SORT_REGULAR, $sort->getSortFlags());
    }
}
