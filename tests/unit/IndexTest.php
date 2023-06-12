<?php

use KSamuel\FacetedSearch\Filter\ExcludeValueFilter;
use KSamuel\FacetedSearch\Filter\RangeFilter;
use KSamuel\FacetedSearch\Filter\ValueFilter;
use PHPUnit\Framework\TestCase;
use KSamuel\FacetedSearch\Index\Factory;

use KSamuel\FacetedSearch\Index\IndexInterface;
use KSamuel\FacetedSearch\Index\Profile;
use KSamuel\FacetedSearch\Indexer\Number\RangeIndexer;
use KSamuel\FacetedSearch\Search;
use KSamuel\FacetedSearch\Query\AggregationQuery;
use KSamuel\FacetedSearch\Query\AggregationSort;
use KSamuel\FacetedSearch\Query\Order;
use KSamuel\FacetedSearch\Query\SearchQuery;

class IndexTest extends TestCase
{
    public function testAggregate(): void
    {
        $index = (new Factory)->create(Factory::ARRAY_STORAGE);
        $storage = $index->getStorage();

        $this->assertTrue($storage->addRecord(112, ['vendor' => 'Tester', 'price' => 100]));
        $this->assertTrue($storage->addRecord(113, ['vendor' => 'Tester2', 'price' => 101]));
        $this->assertTrue($storage->addRecord(114, ['vendor' => 'Tester2', 'price' => 101]));

        $this->assertEquals(
            [
                'vendor' => [
                    'Tester' => 1,
                    'Tester2' => 2
                ],
                'price' => [
                    100 => 1,
                    101 => 2
                ]
            ],
            $index->aggregate((new AggregationQuery())->countItems())
        );
    }

    public function storeProvider(): array
    {
        return [
            [(new Factory)->create(Factory::ARRAY_STORAGE)],
            [(new Factory)->create(Factory::FIXED_ARRAY_STORAGE)]
        ];
    }

    /**
     * @dataProvider storeProvider
     */
    public function testQuery(IndexInterface $index): void
    {
        $records = $this->getTestData();
        $storage = $index->getStorage();

        foreach ($records as $id => $item) {
            $storage->addRecord($id, $item);
        }

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

        $result = $index->query((new SearchQuery())->filters($filters));
        sort($result);
        $this->assertEquals([3, 4], $result);

        $filter = new ValueFilter('vendor');
        $filter->setValue(['Google']);
        $result = $index->query((new SearchQuery())->filters([$filter]));
        $this->assertEquals([], $result);

        $storage->setData($storage->getData());
        $filter = new ValueFilter('vendor_field');
        $filter->setValue(['Google']);
        $result = $index->query((new SearchQuery())->filter($filter)->inRecords([3, 4]));
        $this->assertEquals([], $result);
    }

    /**
     * @dataProvider storeProvider
     */
    public function testQueryLimit(IndexInterface $index): void
    {
        $records = $this->getTestData();
        $storage = $index->getStorage();

        foreach ($records as $id => $item) {
            $storage->addRecord($id, $item);
        }
        $filter = new ValueFilter('vendor');
        $filter->setValue(['Samsung', 'Apple']);
        $result = $index->query((new SearchQuery())->filters([$filter])->inRecords([1, 3]));
        $result = array_flip($result);
        $this->assertArrayHasKey(1, $result);
        $this->assertArrayHasKey(3, $result);
    }

    /**
     * @dataProvider storeProvider
     */
    public function testAggregation(IndexInterface $index): void
    {
        $records = $this->getTestData();
        $storage = $index->getStorage();

        foreach ($records as $id => $item) {
            $storage->addRecord($id, $item);
        }

        $filter = new ValueFilter('color', 'black');

        $acceptableFilters = $index->aggregate((new AggregationQuery())->filter($filter));

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

    /**
     * @dataProvider storeProvider
     */
    public function testAggregationCountNoFilter(IndexInterface $index): void
    {
        $storage = $index->getStorage();

        $records = [
            ['color' => 'black', 'size' => 7, 'group' => 'A'],
            ['color' => 'black', 'size' => 8, 'group' => 'A'],
            ['color' => 'white', 'size' => 7, 'group' => 'B'],
            ['color' => 'yellow', 'size' => 7, 'group' => 'C'],
            ['color' => 'black', 'size' => 7, 'group' => 'C'],
        ];

        foreach ($records as $id => $item) {
            $storage->addRecord($id, $item);
        }

        $acceptableFilters = $index->aggregate((new AggregationQuery())->countItems());

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

    /**
     * @dataProvider storeProvider
     */
    public function testAggregationCountLimit(IndexInterface $index): void
    {
        $storage = $index->getStorage();

        $records = [
            ['id' => 1, 'color' => 'black', 'size' => 7, 'group' => 'A'],
            ['id' => 2, 'color' => 'black', 'size' => 8, 'group' => 'A'],
            ['id' => 3, 'color' => 'white', 'size' => 7, 'group' => 'B'],
            ['id' => 4, 'color' => 'yellow', 'size' => 7, 'group' => 'C'],
            ['id' => 5, 'color' => 'black', 'size' => 7, 'group' => 'C'],
        ];

        foreach ($records as  $item) {
            $storage->addRecord($item['id'], $item);
        }


        $acceptableFilters = $index->aggregate((new AggregationQuery())->inRecords([1, 2])->countItems());

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

    /**
     * @dataProvider storeProvider
     */
    public function testAggregationCount(IndexInterface $index): void
    {
        $records = $this->getTestData();
        $storage = $index->getStorage();

        foreach ($records as $id => $item) {
            $storage->addRecord($id, $item);
        }

        $filter = new ValueFilter('color', 'black');

        $acceptableFilters = $index->aggregate((new AggregationQuery())->filter($filter)->countItems());


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

    /**
     * @dataProvider storeProvider
     */
    public function testAggregationFiltersCountMulti(IndexInterface $index): void
    {
        $storage = $index->getStorage();

        $records = [
            ['color' => 'black', 'size' => 7, 'group' => 'A'],
            ['color' => 'black', 'size' => 8, 'group' => 'A'],
            ['color' => 'white', 'size' => 7, 'group' => 'B'],
            ['color' => 'yellow', 'size' => 7, 'group' => 'C'],
            ['color' => 'black', 'size' => 7, 'group' => 'C'],
        ];

        foreach ($records as $id => $item) {
            $storage->addRecord($id, $item);
        }

        $filter = new ValueFilter('color', 'black');
        $filter2 = new ValueFilter('size', 7);

        $acceptableFilters = $index->aggregate((new AggregationQuery())->filters([$filter, $filter2])->countItems());

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

    /**
     * @dataProvider storeProvider
     */
    public function testIntFilterNames(IndexInterface $index): void
    {
        $storage = $index->getStorage();
        $records = [
            ['id' => 1, 1 => 'black', 2 => 7.5, 'group' => 'A'],
            ['id' => 2, 1 => 'black', 2 => 8.9, 'group' => 'A'],
            ['id' => 3, 1 => 'white', 2 => 7.11, 'group' => 'B'],
        ];
        foreach ($records as $item) {
            $id = $item['id'];
            unset($item['id']);
            $storage->addRecord($id, $item);
        }

        $filter = new ValueFilter(2, 7.11);
        $result = $index->query((new SearchQuery())->filters([$filter]));
        $this->assertEquals(3, $result[0]);

        $filter = new ValueFilter(1, 'black');
        $filter2 = new ValueFilter('group', 'A');
        $result = $index->query((new SearchQuery())->filters([$filter, $filter2]));
        $this->assertEquals(1, $result[0]);
        $this->assertEquals(2, $result[1]);

        $acceptableFilters = $index->aggregate((new AggregationQuery())->filters([$filter, $filter2])->countItems());

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

    /**
     * @dataProvider storeProvider
     */
    public function testFindFloat(IndexInterface $index): void
    {
        $storage = $index->getStorage();
        $records = [
            ['id' => 1, 'color' => 'black', 'size' => 7.5, 'group' => 'A'],
            ['id' => 2, 'color' => 'black', 'size' => 8.9, 'group' => 'A'],
            ['id' => 3, 'color' => 'white', 'size' => 7.11, 'group' => 'B'],
        ];

        foreach ($records as $item) {
            $id = $item['id'];
            unset($item['id']);
            $storage->addRecord($id, $item);
        }

        $filter = new ValueFilter('size', 7.11);
        $result = $index->query((new SearchQuery())->filters([$filter]));
        $this->assertEquals(3, $result[0]);

        $filter = new ValueFilter('size', '8.9');
        $result = $index->query((new SearchQuery())->filters([$filter]));
        $this->assertEquals(2, $result[0]);


        $acceptableFilters = $index->aggregate((new AggregationQuery())->filters([$filter])->countItems());

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

    /**
     * @dataProvider storeProvider
     */
    public function testOrderedSearch(IndexInterface $index): void
    {
        $storage = $index->getStorage();
        $records = [
            ['id' => 1, 'color' => 'black', 'size' => 7.5, 'group' => 'A'],
            ['id' => 2, 'color' => 'black', 'size' => 8.9, 'group' => 'A'],
            ['id' => 3, 'color' => 'white', 'size' => 7.11, 'group' => 'B'],
            ['id' => 4, 'color' => 'white', 'size' => 9, 'group' => 'C'],
            ['id' => 5, 'color' => 'white', 'size' => 3, 'group' => 'C'],
        ];

        foreach ($records as $item) {
            $id = $item['id'];
            unset($item['id']);
            $storage->addRecord($id, $item);
        }

        $query = (new SearchQuery())->order('size', Order::SORT_DESC);
        $results = $index->query($query);

        $this->assertEquals([4, 2, 1, 3, 5], $results);


        $query = (new SearchQuery())
            ->filter(new ValueFilter('group', 'C'))
            ->order('size', Order::SORT_ASC);
        $results = $index->query($query);

        $this->assertEquals([5, 4], $results);
    }
    /**
     * @dataProvider storeProvider
     */
    public function testAggregationSort(IndexInterface $index): void
    {
        $storage = $index->getStorage();
        $records = [
            ['size' => 7, 'color' => 'yellow', 'group' => 'C'],
            ['color' => 'black', 'size' => 7, 'group' => 'C'],
            ['color' => 'black', 'size' => 7, 'group' => 'A'],
            ['color' => 'black', 'size' => 8, 'group' => 'A'],
            ['color' => 'white', 'size' => 7, 'group' => 'B'],
        ];

        foreach ($records as $id => $item) {
            $storage->addRecord($id, $item);
        }

        $acceptableFilters = $index->aggregate((new AggregationQuery())->countItems()->sort());

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

        $acceptableFilters = $index->aggregate((new AggregationQuery())->countItems()->sort(AggregationSort::SORT_DESC));

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
    /**
     * @dataProvider storeProvider
     */
    public function testNoInput(IndexInterface $index): void
    {
        $storage = $index->getStorage();
        $records = [
            ['id' => 1, 'color' => 'black', 'size' => 7, 'group' => 'A'],
            ['id' => 2, 'color' => 'black', 'size' => 8, 'group' => 'A'],
            ['id' => 3, 'color' => 'white', 'size' => 9, 'group' => 'B'],
        ];
        foreach ($records as $item) {
            $id = $item['id'];
            unset($item['id']);
            $storage->addRecord($id, $item);
        }

        $acceptableFilters = $index->aggregate((new AggregationQuery())->sort());

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
    /**
     * @dataProvider storeProvider
     */
    public function testNoInputButExclude(IndexInterface $index): void
    {
        $storage = $index->getStorage();
        $records = [
            ['id' => 1, 'color' => 'black', 'size' => 7, 'group' => 'A'],
            ['id' => 2, 'color' => 'black', 'size' => 8, 'group' => 'A'],
            ['id' => 3, 'color' => 'white', 'size' => 9, 'group' => 'B'],
        ];
        foreach ($records as $item) {
            $id = $item['id'];
            unset($item['id']);
            $storage->addRecord($id, $item);
        }

        $acceptableFilters = $index->aggregate(
            (new AggregationQuery())
                ->filter(
                    new ExcludeValueFilter('color', ['white'])
                )->sort()
        );

        $expect = [
            'color' => ['black' => true],
            'group' => ['A' => true],
            'size' => [7 => true, 8 => true],
        ];

        $this->assertEquals(array_keys($expect), array_keys($acceptableFilters));
        foreach ($expect as $field => $values) {
            $this->assertEquals(array_keys($expect[$field]), array_keys($values));
            $this->assertEquals(array_values($expect[$field]), array_values($values));
        }
    }
    /**
     * @dataProvider storeProvider
     */
    public function testNoInputButExcludeQuery(IndexInterface $index): void
    {
        $storage = $index->getStorage();
        $records = [
            ['id' => 1, 'color' => 'black', 'size' => 7, 'group' => 'A'],
            ['id' => 2, 'color' => 'black', 'size' => 8, 'group' => 'A'],
            ['id' => 3, 'color' => 'white', 'size' => 9, 'group' => 'B'],
        ];
        foreach ($records as $item) {
            $id = $item['id'];
            unset($item['id']);
            $storage->addRecord($id, $item);
        }

        $data = $index->query(
            (new SearchQuery())
                ->filter(
                    new ExcludeValueFilter('color', ['white'])
                )->order('size', Order::SORT_ASC)
        );

        $this->assertEquals([1, 2], $data);
    }
    /**
     * @dataProvider storeProvider
     */
    public function testNoInputQuery(IndexInterface $index): void
    {
        $storage = $index->getStorage();
        $records = [
            ['id' => 1, 'color' => 'black', 'size' => 7, 'group' => 'A'],
            ['id' => 2, 'color' => 'black', 'size' => 8, 'group' => 'A'],
            ['id' => 3, 'color' => 'white', 'size' => 9, 'group' => 'B'],
        ];
        foreach ($records as $item) {
            $id = $item['id'];
            unset($item['id']);
            $storage->addRecord($id, $item);
        }

        $data = $index->query(
            (new SearchQuery())->order('size', Order::SORT_ASC)
        );

        $this->assertEquals([1, 2, 3], $data);
    }

    public function testGetCount(): void
    {
        $index = (new Factory)->create(Factory::ARRAY_STORAGE);
        $storage = $index->getStorage();
        $storage->addRecord(1, ['col' => 2]);
        $storage->addRecord(2, ['col' => 2, 'pr' => 1, 'dr' => 2]);
        $storage->addRecord(3, ['col' => 2, 'pr' => 1, 'dr' => 3]);
        $this->assertEquals(3, $index->getCount());
    }

    public function testSetProfile(): void
    {
        $index = (new Factory)->create(Factory::ARRAY_STORAGE);
        $profile = new Profile();
        $index->setProfiler($profile);
        $storage = $index->getStorage();
        $storage->addRecord(1, ['col' => 2]);
        $storage->addRecord(2, ['col' => 2, 'pr' => 1, 'dr' => 2]);
        $storage->addRecord(3, ['col' => 2, 'pr' => 1, 'dr' => 3]);
        $index->query((new SearchQuery())->order('col'));
        $this->assertTrue($profile->getSortingTime() > 0);
    }

    public function testSetData(): void
    {
        $index = (new Factory)->create(Factory::ARRAY_STORAGE);
        $data = [
            'field1' => [
                'val1' => [1, 2, 3],
                'val2' => [2, 3, 4]
            ],
            'field2' => [
                'val1' => [1],
                'val2' => [3, 4]
            ]
        ];

        $index->setData($data);
        $this->assertEquals($data, $index->export());
    }

    public function testAggregationEmptySort(): void
    {
        $index = (new Factory)->create(Factory::ARRAY_STORAGE);
        $data = [
            'field2' => [
                'val2' => [1, 2, 3],
                'val1' => [2, 3, 4]
            ],
            'field1' => [
                'val2' => [1],
                'val1' => [3, 4]
            ]
        ];

        $index->setData($data);
        $result = $index->aggregate((new AggregationQuery())->sort()->countItems());
        $expected = [
            'field1' => [
                'val1' => 2,
                'val2' => 1,

            ],
            'field2' => [
                'val1' => 3,
                'val2' => 3,

            ],
        ];
        $this->assertEquals($expected, $result);
    }

    public function testFilterCombination(): void
    {
        $this->filterCombination((new Factory)->create(Factory::ARRAY_STORAGE));
        $this->filterCombination((new Factory)->create(Factory::FIXED_ARRAY_STORAGE));
    }

    private function filterCombination(IndexInterface $index): void
    {
        $records = $this->getTestData();
        $storage = $index->getStorage();

        $storage->addIndexer('price_range', new RangeIndexer(50000));

        foreach ($records as $id => $item) {
            $item['price_range'] = $item['price'];
            $storage->addRecord($id, $item);
        }
        $storage->optimize();

        $filters = [
            new ValueFilter('color', 'black'),
            new ExcludeValueFilter('vendor', 'Xiaomi'),
            new ValueFilter('price_range',  50000)
        ];

        $acceptableFilters = $index->aggregate((new AggregationQuery())->filters($filters)->countItems());

        $expect = [
            'vendor' => ['Apple' => 1, 'Samsung' => 1],
            'model' => ['Iphone X Pro Max' => 1, 'Galaxy S20' => 1],
            'price' => [80999 => 1, 70599 => 1],
            // self filtering is not using by facets logic
            'color' => ['black' => 2, 'white' => 1, 'yellow' => 1],
            'has_phones' => [1 => 2],
            'cam_mp' => [40 => 1, 105 => 1],
            'sale' => [1 => 2],
            'price_range' => [0 => 1, 50000 => 2]
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
