<?php

use PHPUnit\Framework\TestCase;
use KSamuel\FacetedSearch\Query\Order;

class OrderTest extends TestCase
{
    public function testCreate(): void
    {
        $order = new Order('field', Order::SORT_ASC);
        $this->assertEquals('field', $order->getField());
        $this->assertEquals(Order::SORT_ASC, $order->getDirection());
        $this->assertEquals(SORT_REGULAR, $order->getSortFlags());
    }
}
