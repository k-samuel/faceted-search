<?php

use PHPUnit\Framework\TestCase;
use KSamuel\FacetedSearch\Filter\ValueFilter;
use KSamuel\FacetedSearch\Filter\ValueIntersectionFilter;
use KSamuel\FacetedSearch\Index\Factory;
use KSamuel\FacetedSearch\Index\IndexInterface;
use KSamuel\FacetedSearch\Query\AggregationQuery;

class SelfFilterTest extends TestCase
{
    private function getIndex($type): IndexInterface
    {
        $index = (new Factory)->create(Factory::ARRAY_STORAGE);
        $storage = $index->getStorage();
        $data = [
            1 => [
                'brand' => 'Nony',
                'first_usage' => ['weddings', 'wildlife'],
                'second_usage' =>  ['wildlife', 'portraits']
            ],
            2 => [
                'brand' => 'Mikon',
                'first_usage' => ['weddings', 'streetphoto'],
                'second_usage' =>  ['wildlife', 'streetphoto']
            ],
            3 => [
                'brand' => 'Common',
                'first_usage' => ['streetphoto', 'portraits'],
                'second_usage' =>  ['streetphoto', 'portraits']
            ],
            4 => [
                'brand' => 'Digma',
                'first_usage' => ['streetphoto', 'portraits', 'weddings'],
                'second_usage' =>  ['streetphoto', 'portraits']
            ],
            5 => [
                'brand' => 'Digma',
                'first_usage' => ['streetphoto'],
                'second_usage' =>  ['portraits']
            ],
            6 => [
                'brand' => 'Mikon',
                'first_usage' => ['weddings', 'wildlife'],
                'second_usage' =>  ['wildlife', 'portraits']
            ]
        ];
        foreach ($data as $k => $v) {
            $storage->addRecord($k, $v);
        }
        $storage->optimize();
        return $index;
    }
    public function testMixedFiltering(): void
    {
        $query1 = (new AggregationQuery())->filters([
            new ValueFilter('brand', ['Nony', 'Digma', 'Mikon']),
            (new ValueIntersectionFilter('first_usage', ['weddings']))->selfFiltering(true),
        ])->countItems()->sort();

        $query2 = (new AggregationQuery())->filters([
            new ValueFilter('brand', ['Nony', 'Digma']),
            (new ValueIntersectionFilter('first_usage', ['weddings']))->selfFiltering(true),
        ])->countItems()->sort();

        $expect = [
            'brand' => [
                'Digma' => 1,
                'Mikon' => 2,
                'Nony' => 1
            ],
            'first_usage' => [
                'portraits' => 1,
                'streetphoto' => 2,
                'weddings' => 4,
                'wildlife' => 2
            ],
            'second_usage' => [
                'portraits' => 3,
                'streetphoto' => 2,
                'wildlife' => 3,
            ],
        ];

        $expect2 = [
            'brand' => [
                'Digma' => 1,
                'Nony' => 1,
                'Mikon' => 2,
            ],
            'first_usage' => [
                'portraits' => 1,
                'streetphoto' => 1,
                'weddings' => 2,
                'wildlife' => 1
            ],
            'second_usage' => [
                'portraits' => 2,
                'streetphoto' => 1,
                'wildlife' => 1,
            ],
        ];

        $index = $this->getIndex(Factory::ARRAY_STORAGE);
        $result = $index->aggregate($query1);

        $this->assertEquals($expect, $result);

        $result = $index->aggregate($query2);
        $this->assertEquals($expect2, $result);


        $index = $this->getIndex(Factory::FIXED_ARRAY_STORAGE);
        $result = $index->aggregate($query1);

        $this->assertEquals($expect, $result);

        $result = $index->aggregate($query2);
        $this->assertEquals($expect2, $result);
    }
}
