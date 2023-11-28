<?php

use KSamuel\FacetedSearch\Filter\ExcludeValueFilter;
use PHPUnit\Framework\TestCase;
use KSamuel\FacetedSearch\Filter\ValueFilter;
use KSamuel\FacetedSearch\Filter\ValueIntersectionFilter;
use KSamuel\FacetedSearch\Index\ArrayIndex;
use KSamuel\FacetedSearch\Index\Factory;
use KSamuel\FacetedSearch\Index\IndexInterface;
use KSamuel\FacetedSearch\Index\Storage\FixedArrayStorage;
use KSamuel\FacetedSearch\Index\Storage\StorageInterface;
use KSamuel\FacetedSearch\Query\AggregationQuery;
use KSamuel\FacetedSearch\Query\SearchQuery;
use KSamuel\FacetedSearch\Search;

class ValueIntersectionFilterTest extends TestCase
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

    public function testQuery(): void
    {
        $query1 = (new SearchQuery())->filters([
            new ValueFilter('brand', ['Nony', 'Digma', 'Mikon', 'Common']),
            new ValueIntersectionFilter('first_usage', ['streetphoto', 'weddings']),
        ]);

        $query2 = (new SearchQuery())->filters([
            new ValueFilter('brand', ['Mikon', 'Digma']),
            new ValueIntersectionFilter('first_usage', ['streetphoto', 'weddings']),
            new ValueIntersectionFilter('second_usage', ['streetphoto', 'portraits']),
        ]);

        $query3 = (new SearchQuery())->filters([
            new ValueFilter('brand', ['Nony', 'Digma', 'Mikon', 'Common']),
            new ValueIntersectionFilter('first_usage', ['streetphoto', 'weddings']),
            new ExcludeValueFilter('brand', ['Digma'])
        ]);

        $query4 = (new SearchQuery())->filters([
            new ValueFilter('brand', ['Nony', 'Digma', 'Mikon', 'Common']),
            new ValueIntersectionFilter('first_usage', ['streetphoto', 'weddings']),
        ])->inRecords([1, 3, 4]);

        $index = $this->getIndex(Factory::ARRAY_STORAGE);
        $result = $index->query($query1);
        sort($result);
        $this->assertEquals([2, 4], $result);
        $this->assertEquals([4], $index->query($query2));
        $this->assertEquals([2], $index->query($query3));
        $this->assertEquals([4], $index->query($query4));

        $index = $this->getIndex(Factory::FIXED_ARRAY_STORAGE);
        $result = $index->query($query1);
        sort($result);
        $this->assertEquals([2, 4], $result);
        $this->assertEquals([4], $index->query($query2));
        $this->assertEquals([2], $index->query($query3));
        $this->assertEquals([4], $index->query($query4));
    }

    public function testAggregate(): void
    {
        $query1 = (new AggregationQuery())->filters([
            new ValueFilter('brand', ['Mikon', 'Digma']),
            new ValueIntersectionFilter('first_usage', ['streetphoto', 'weddings']),
            new ValueIntersectionFilter('second_usage', ['streetphoto', 'portraits']),
        ])->countItems()->sort();

        $query2 = (new AggregationQuery())->filters([
            new ValueFilter('brand', ['Mikon', 'Digma']),
            new ValueIntersectionFilter('first_usage', ['streetphoto', 'weddings']),
            new ValueIntersectionFilter('second_usage', ['streetphoto', 'portraits']),
        ])->countItems()->sort()->selfFiltering(true);

        $index = $this->getIndex(Factory::ARRAY_STORAGE);
        $result = $index->aggregate($query1);
        $this->assertEquals([
            'brand' => [
                'Digma' => 1,
            ],
            'first_usage' => [
                'streetphoto' => 1,
                'weddings' => 1,
                'portraits' => 1,
            ],
            'second_usage' => [
                'streetphoto' => 2,
                'wildlife' => 1,
                'portraits' => 1,
            ],
        ], $result);

        $result = $index->aggregate($query2);
        $this->assertEquals([
            'brand' => [
                'Digma' => 1,
            ],
            'first_usage' => [
                'streetphoto' => 1,
                'weddings' => 1,
                'portraits' => 1,
            ],
            'second_usage' => [
                'streetphoto' => 1,
                'portraits' => 1,
            ],
        ], $result);


        $index = $this->getIndex(Factory::FIXED_ARRAY_STORAGE);
        $result = $index->aggregate($query1);
        $this->assertEquals([
            'brand' => [
                'Digma' => 1,
            ],
            'first_usage' => [
                'streetphoto' => 1,
                'weddings' => 1,
                'portraits' => 1,
            ],
            'second_usage' => [
                'streetphoto' => 2,
                'wildlife' => 1,
                'portraits' => 1,
            ],
        ], $result);

        $result = $index->aggregate($query2);
        $this->assertEquals([
            'brand' => [
                'Digma' => 1,
            ],
            'first_usage' => [
                'streetphoto' => 1,
                'weddings' => 1,
                'portraits' => 1,
            ],
            'second_usage' => [
                'streetphoto' => 1,
                'portraits' => 1,
            ],
        ], $result);
    }
}
