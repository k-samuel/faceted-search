<?php
use PHPUnit\Framework\TestCase;
use KSamuel\FacetedSearch\Filter\RangeFilter;

class RangeFilterTest extends TestCase
{
    public function testSetWrongValueException()
    {
        $filter = new RangeFilter('field');
        $this->expectException('Exception');
        $filter->setValue(1);
    }

    public function testSetEmptyValueException()
    {
        $filter = new RangeFilter('field');
        $this->expectException('Exception');
        $filter->setValue(['min'=>null, 'max'=>null]);
    }

    public function testSetValue()
    {
        $filter = new RangeFilter('field');
        $filter->setValue(['min'=>10]);

        $this->assertEquals(['min'=>10,'max'=>null], $filter->getValue());
    }
}