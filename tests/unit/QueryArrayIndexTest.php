<?php

use PHPUnit\Framework\TestCase;
use KSamuel\FacetedSearch\Search;
use KSamuel\FacetedSearch\Filter\ValueFilter;
use KSamuel\FacetedSearch\Filter\RangeFilter;
use KSamuel\FacetedSearch\Index;
use KSamuel\FacetedSearch\Index\ArrayIndex;
use KSamuel\FacetedSearch\Query\AggregationQuery;
use KSamuel\FacetedSearch\Query\AggregationSort;
use KSamuel\FacetedSearch\Query\Order;
use KSamuel\FacetedSearch\Query\SearchQuery;

class QueryArrayIndexTest extends TestCase
{

    public function testFind(): void
    {
        $records = $this->getTestData();
        $index = new ArrayIndex();

        foreach ($records as $id => $item) {
            $index->addRecord($id, $item);
        }
        $facets = new Search($index);
        $filter = new ValueFilter('vendor');
        $filter->setValue(['Samsung', 'Apple']);

        $filter2 = new RangeFilter('cam_mp');
        $filter2->setValue(['min' => 16]);

        $filter3 = new RangeFilter('price');
        $filter3->setValue(['max' => 80000]);

        $filter4 = new ValueFilter('sale');
        $filter4->setValue(true);

        $filters = [
            $filter,
            $filter2,
            $filter3,
            $filter4
        ];

        $result = $facets->query((new SearchQuery())->filters($filters));
        sort($result);
        $this->assertEquals([3, 4], $result);

        $result = $facets->find($filters, array_keys($records));
        sort($result);
        $this->assertEquals([3, 4], $result);

        $filter = new ValueFilter('vendor');
        $filter->setValue(['Google']);
        $result = $facets->find([$filter]);
        $this->assertEquals([], $result);

        $index->setData($index->getData());
        $filter = new ValueFilter('vendor_field');
        $filter->setValue(['Google']);
        $result = $facets->query((new SearchQuery())->filter($filter)->inRecords([3, 4]));
        $this->assertEquals([], $result);
    }

    public function testFindWithLimit(): void
    {
        $records = $this->getTestData();
        $index = new ArrayIndex();

        foreach ($records as $id => $item) {
            $index->addRecord($id, $item);
        }
        $facets = new Search($index);
        $filter = new ValueFilter('vendor');
        $filter->setValue(['Samsung', 'Apple']);
        $result = $facets->query((new SearchQuery())->filters([$filter])->inRecords([1, 3]));
        $result = array_flip($result);
        $this->assertArrayHasKey(1, $result);
        $this->assertArrayHasKey(3, $result);
    }

    public function testGetAcceptableFilters(): void
    {
        $records = $this->getTestData();
        $index = new Index\ArrayIndex();
        foreach ($records as $id => $item) {
            $index->addRecord($id, $item);
        }
        $facets = new Search($index);
        $filter = new ValueFilter('color', 'black');

        $acceptableFilters = $facets->aggregate((new AggregationQuery())->filter($filter));

        $expect = [
            'vendor' => ['Apple' => true, 'Samsung' => true, 'Xiaomi' => true],
            'model' => ['Iphone X Pro Max' => true, 'Galaxy S20' => true, 'Galaxy A5' => true, 'MI 9' => true],
            'price' => [80999 => true, 70599 => true, 15000 => true, 26000 => true],
            'color' => ['black' => true, 'white' => true, 'yellow' => true],
            'has_phones' => [1 => true],
            'cam_mp' => [40 => true, 105 => true, 12 => true, 48 => true],
            'sale' => [1 => true, 0 => true]
        ];
        foreach ($expect as $field => &$values) {
            sort($values);
        }
        unset($values);
        foreach ($acceptableFilters as $field => &$values) {
            sort($values);
        }
        unset($values);

        foreach ($expect as $filter => $values) {
            $this->assertArrayHasKey($filter, $acceptableFilters);
            $this->assertEquals($values, $acceptableFilters[$filter]);
        }
    }

    public function testGetAcceptableFiltersCountNoFilters(): void
    {
        $records = [
            ['color' => 'black', 'size' => 7, 'group' => 'A'],
            ['color' => 'black', 'size' => 8, 'group' => 'A'],
            ['color' => 'white', 'size' => 7, 'group' => 'B'],
            ['color' => 'yellow', 'size' => 7, 'group' => 'C'],
            ['color' => 'black', 'size' => 7, 'group' => 'C'],
        ];
        $index = new ArrayIndex();
        foreach ($records as $id => $item) {
            $index->addRecord($id, $item);
        }
        $facets = new Search($index);

        $acceptableFilters = $facets->aggregate((new AggregationQuery())->countItems());

        $expect = [
            'color' => ['black' => 3, 'white' => 1, 'yellow' => 1],
            'size' => [7 => 4, 8 => 1],
            'group' => ['A' => 2, 'B' => 1, 'C' => 2],
        ];
        foreach ($expect as $field => &$values) {
            asort($values);
        }
        unset($values);
        foreach ($acceptableFilters as $field => &$values) {
            asort($values);
        }
        unset($values);

        foreach ($expect as $filter => $values) {
            $this->assertArrayHasKey($filter, $acceptableFilters);
            $this->assertEquals($values, $acceptableFilters[$filter]);
        }
    }

    public function testGetAcceptableFiltersCountLimit(): void
    {
        $records = [
            ['id' => 1, 'color' => 'black', 'size' => 7, 'group' => 'A'],
            ['id' => 2, 'color' => 'black', 'size' => 8, 'group' => 'A'],
            ['id' => 3, 'color' => 'white', 'size' => 7, 'group' => 'B'],
            ['id' => 4, 'color' => 'yellow', 'size' => 7, 'group' => 'C'],
            ['id' => 5, 'color' => 'black', 'size' => 7, 'group' => 'C'],
        ];
        $index = new ArrayIndex();
        foreach ($records as  $item) {
            $index->addRecord($item['id'], $item);
        }
        $facets = new Search($index);

        $acceptableFilters = $facets->aggregate((new AggregationQuery())->inRecords([1, 2])->countItems());

        $expect = [
            'color' => ['black' => 2],
            'size' => [7 => 1, 8 => 1],
            'group' => ['A' => 2],
        ];
        foreach ($expect as $field => &$values) {
            asort($values);
        }
        unset($values);
        foreach ($acceptableFilters as $field => &$values) {
            asort($values);
        }
        unset($values);

        foreach ($expect as $filter => $values) {
            $this->assertArrayHasKey($filter, $acceptableFilters);
            $this->assertEquals($values, $acceptableFilters[$filter]);
        }
    }

    public function testGetAcceptableFiltersCount(): void
    {
        $records = $this->getTestData();
        $index = new ArrayIndex();
        foreach ($records as $id => $item) {
            $index->addRecord($id, $item);
        }
        $facets = new Search($index);
        $filter = new ValueFilter('color', 'black');

        $acceptableFilters = $facets->aggregate((new AggregationQuery())->filter($filter)->countItems());


        $expect = [
            'vendor' => ['Apple' => 1, 'Samsung' => 2, 'Xiaomi' => 1],
            'model' => ['Iphone X Pro Max' => 1, 'Galaxy S20' => 1, 'Galaxy A5' => 1, 'MI 9' => 1],
            'price' => [80999 => 1, 70599 => 1, 15000 => 1, 26000 => 1],
            // self filtering is not using by facets logic
            'color' => ['black' => 4, 'white' => 1, 'yellow' => 1],
            'has_phones' => [1 => 4],
            'cam_mp' => [40 => 1, 105 => 1, 12 => 1, 48 => 1],
            'sale' => [1 => 3, 0 => 1]
        ];
        foreach ($expect as $field => &$values) {
            asort($values);
        }
        unset($values);
        foreach ($acceptableFilters as $field => &$values) {
            asort($values);
        }
        unset($values);

        foreach ($expect as $filter => $values) {
            $this->assertArrayHasKey($filter, $acceptableFilters);
            $this->assertEquals($values, $acceptableFilters[$filter]);
        }
    }

    public function testGetAcceptableFiltersCountMulti(): void
    {
        $records = [
            ['color' => 'black', 'size' => 7, 'group' => 'A'],
            ['color' => 'black', 'size' => 8, 'group' => 'A'],
            ['color' => 'white', 'size' => 7, 'group' => 'B'],
            ['color' => 'yellow', 'size' => 7, 'group' => 'C'],
            ['color' => 'black', 'size' => 7, 'group' => 'C'],
        ];
        $index = new ArrayIndex();
        foreach ($records as $id => $item) {
            $index->addRecord($id, $item);
        }
        $facets = new Search($index);
        $filter = new ValueFilter('color', 'black');
        $filter2 = new ValueFilter('size', 7);

        $acceptableFilters =  $facets->aggregate((new AggregationQuery())->filters([$filter, $filter2])->countItems());

        $expect = [
            'color' => ['black' => 2, 'white' => 1, 'yellow' => 1],
            'size' => [7 => 2, 8 => 1],
            'group' => ['A' => 1, 'C' => 1],
        ];
        foreach ($expect as $field => &$values) {
            asort($values);
        }
        unset($values);
        foreach ($acceptableFilters as $field => &$values) {
            asort($values);
        }
        unset($values);

        foreach ($expect as $filter => $values) {
            $this->assertArrayHasKey($filter, $acceptableFilters);
            $this->assertEquals($values, $acceptableFilters[$filter]);
        }
    }

    public function testIntFilterNames(): void
    {
        $index = new ArrayIndex();
        $records = [
            ['id' => 1, 1 => 'black', 2 => 7.5, 'group' => 'A'],
            ['id' => 2, 1 => 'black', 2 => 8.9, 'group' => 'A'],
            ['id' => 3, 1 => 'white', 2 => 7.11, 'group' => 'B'],
        ];
        foreach ($records as $item) {
            $id = $item['id'];
            unset($item['id']);
            $index->addRecord($id, $item);
        }
        $facets = new Search($index);
        $filter = new ValueFilter(2, 7.11);
        $result = $facets->query((new SearchQuery())->filters([$filter]));
        $this->assertEquals(3, $result[0]);

        $filter = new ValueFilter(1, 'black');
        $filter2 = new ValueFilter('group', 'A');
        $result = $facets->query((new SearchQuery())->filters([$filter, $filter2]));
        $this->assertEquals(1, $result[0]);
        $this->assertEquals(2, $result[1]);

        $acceptableFilters =  $facets->aggregate((new AggregationQuery())->filters([$filter, $filter2])->countItems());

        $expect = [
            1 => ['black' => 2],
            2 => ['8.9' => 1, '7.5' => 1],
            'group' => ['A' => 2]
        ];
        foreach ($expect as  &$values) {
            asort($values);
        }
        unset($values);
        foreach ($acceptableFilters as &$values) {
            asort($values);
        }
        unset($values);

        foreach ($expect as $filter => $values) {
            $this->assertArrayHasKey($filter, $acceptableFilters);
            $this->assertEquals($values, $acceptableFilters[$filter]);
        }
    }

    public function testFindFloat(): void
    {
        $records = [
            ['id' => 1, 'color' => 'black', 'size' => 7.5, 'group' => 'A'],
            ['id' => 2, 'color' => 'black', 'size' => 8.9, 'group' => 'A'],
            ['id' => 3, 'color' => 'white', 'size' => 7.11, 'group' => 'B'],
        ];
        $index = new ArrayIndex();
        foreach ($records as $item) {
            $id = $item['id'];
            unset($item['id']);
            $index->addRecord($id, $item);
        }
        $facets = new Search($index);

        $filter = new ValueFilter('size', 7.11);
        $result = $facets->query((new SearchQuery())->filters([$filter]));
        $this->assertEquals(3, $result[0]);

        $filter = new ValueFilter('size', '8.9');
        $result = $facets->query((new SearchQuery())->filters([$filter]));
        $this->assertEquals(2, $result[0]);


        $acceptableFilters =  $facets->aggregate((new AggregationQuery())->filters([$filter])->countItems());

        $expect = [
            'color' => ['black' => 1],
            'size' => ['8.9' => 1, '7.5' => 1, '7.11' => 1],
            'group' => ['A' => 1]
        ];
        foreach ($expect as  &$values) {
            asort($values);
        }
        unset($values);
        foreach ($acceptableFilters as &$values) {
            asort($values);
        }
        unset($values);

        foreach ($expect as $filter => $values) {
            $this->assertArrayHasKey($filter, $acceptableFilters);
            $this->assertEquals($values, $acceptableFilters[$filter]);
        }
    }

    public function testOrderedSearch(): void
    {
        $records = [
            ['id' => 1, 'color' => 'black', 'size' => 7.5, 'group' => 'A'],
            ['id' => 2, 'color' => 'black', 'size' => 8.9, 'group' => 'A'],
            ['id' => 3, 'color' => 'white', 'size' => 7.11, 'group' => 'B'],
            ['id' => 4, 'color' => 'white', 'size' => 9, 'group' => 'C'],
            ['id' => 5, 'color' => 'white', 'size' => 3, 'group' => 'C'],
        ];
        $index = new ArrayIndex();
        foreach ($records as $item) {
            $id = $item['id'];
            unset($item['id']);
            $index->addRecord($id, $item);
        }
        $facets = new Search($index);
        $query = (new SearchQuery())->order('size', Order::SORT_DESC);
        $results = $facets->query($query);

        $this->assertEquals([4, 2, 1, 3, 5], $results);

        $facets = new Search($index);
        $query = (new SearchQuery())
            ->filter(new ValueFilter('group', 'C'))
            ->order('size', Order::SORT_ASC);
        $results = $facets->query($query);

        $this->assertEquals([5, 4], $results);
    }
    public function testAggregationSort(): void
    {
        $records = [
            ['size' => 7, 'color' => 'yellow', 'group' => 'C'],
            ['color' => 'black', 'size' => 7, 'group' => 'C'],
            ['color' => 'black', 'size' => 7, 'group' => 'A'],
            ['color' => 'black', 'size' => 8, 'group' => 'A'],
            ['color' => 'white', 'size' => 7, 'group' => 'B'],
        ];
        $index = new ArrayIndex();
        foreach ($records as $id => $item) {
            $index->addRecord($id, $item);
        }
        $facets = new Search($index);

        $acceptableFilters = $facets->aggregate((new AggregationQuery())->countItems()->sort());

        $expect = [
            'color' => ['black' => 3,  'white' => 1, 'yellow' => 1],
            'group' => ['A' => 2, 'B' => 1, 'C' => 2],
            'size' => [7 => 4, 8 => 1],
        ];

        $this->assertEquals(array_keys($expect), array_keys($acceptableFilters));
        foreach ($expect as $field => $values) {
            $this->assertEquals(array_keys($expect[$field]), array_keys($values));
            $this->assertEquals(array_values($expect[$field]), array_values($values));
        }


        $acceptableFilters = $facets->aggregate((new AggregationQuery())->countItems()->sort(AggregationSort::SORT_DESC));

        $expect = [
            'size' => [8 => 1, 7 => 4],
            'group' => ['C' => 2, 'B' => 1, 'A' => 2],
            'color' => ['yellow' => 1, 'white' => 1, 'black' => 3],
        ];

        $this->assertEquals(array_keys($expect), array_keys($acceptableFilters));
        foreach ($expect as $field => $values) {
            $this->assertEquals(array_keys($expect[$field]), array_keys($values));
            $this->assertEquals(array_values($expect[$field]), array_values($values));
        }
    }

    public function testNoInput(): void
    {
        $index = new ArrayIndex();
        $records = [
            ['id' => 1, 'color' => 'black', 'size' => 7, 'group' => 'A'],
            ['id' => 2, 'color' => 'black', 'size' => 8, 'group' => 'A'],
            ['id' => 3, 'color' => 'white', 'size' => 9, 'group' => 'B'],
        ];
        foreach ($records as $item) {
            $id = $item['id'];
            unset($item['id']);
            $index->addRecord($id, $item);
        }
        $facets = new Search($index);
        $acceptableFilters = $acceptableFilters = $facets->aggregate((new AggregationQuery())->sort());

        $expect = [
            'color' => ['black' => true,  'white' => true],
            'group' => ['A' => true, 'B' => true],
            'size' => [7 => true, 8 => true, 9 => true],
        ];

        $this->assertEquals(array_keys($expect), array_keys($acceptableFilters));
        foreach ($expect as $field => $values) {
            $this->assertEquals(array_keys($expect[$field]), array_keys($values));
            $this->assertEquals(array_values($expect[$field]), array_values($values));
        }
    }

    public function getTestData(): array
    {
        return [
            1 => [
                'vendor' => 'Apple',
                'model' => 'Iphone X',
                'price' => 80999,
                'color' => 'white',
                'has_phones' => false,
                'cam_mp' => 20,
                'sale' => true,
            ],
            2 => [
                'vendor' => 'Apple',
                'model' => 'Iphone X Pro Max',
                'price' => 80999,
                'color' => 'black',
                'has_phones' => true,
                'cam_mp' => 40,
                'sale' => true,
            ],
            3 => [
                'vendor' => 'Samsung',
                'model' => 'Galaxy S20',
                'price' => 70599,
                'color' => 'yellow',
                'has_phones' => true,
                'cam_mp' => 105,
                'sale' => true,
            ],
            4 => [
                'vendor' => 'Samsung',
                'model' => 'Galaxy S20',
                'price' => 70599,
                'color' => 'black',
                'has_phones' => true,
                'cam_mp' => 105,
                'sale' => true,
            ],
            5 => [
                'vendor' => 'Samsung',
                'model' => 'Galaxy A5',
                'price' => 15000,
                'color' => 'black',
                'has_phones' => true,
                'cam_mp' => 12,
                'sale' => true,
            ],
            6 => [
                'vendor' => 'Xiaomi',
                'model' => 'MI 9',
                'price' => 26000,
                'color' => 'black',
                'has_phones' => true,
                'cam_mp' => 48,
                'sale' => false,
            ]
        ];
    }
}
