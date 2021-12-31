<?php

use PHPUnit\Framework\TestCase;
use KSamuel\FacetedSearch\Search;
use KSamuel\FacetedSearch\Filter\ValueFilter;
use KSamuel\FacetedSearch\Filter\RangeFilter;
use KSamuel\FacetedSearch\Index;

class SearchFixedArrayIndexTest extends TestCase
{

    protected function loadIndex(array $records) : Index\FixedArrayIndex{
        $index = new Index\FixedArrayIndex();
        $index->writeMode();
        foreach ($records as $id => $item) {
            if(isset($item['id'])){
                $id = $item['id'];
                unset($item['id']);
            }
            $index->addRecord($id, $item);
        }
        $index->commitChanges();
        return $index;
    }

    public function testFind()
    {
        $records = $this->getTestData();
        $index = $this->loadIndex($records);

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
        $result = $facets->find($filters);
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
        $result = $facets->find([$filter], [3, 4]);
        $this->assertEquals([], $result);
    }

    public function testFindWithLimit()
    {
        $records = $this->getTestData();
        $index = $this->loadIndex($records);

        $facets = new Search($index);
        $filter = new ValueFilter('vendor');
        $filter->setValue(['Samsung', 'Apple']);
        $result = $facets->find([$filter], [1, 3]);
        $result = array_flip($result);
        $this->assertArrayHasKey(1, $result);
        $this->assertArrayHasKey(3, $result);
    }

    public function testGetAcceptableFilters()
    {
        $records = $this->getTestData();
        $index = $this->loadIndex($records);

        $facets = new Search($index);
        $filter = new ValueFilter('color', 'black');

        $acceptableFilters = $facets->findAcceptableFilters([$filter]);

        $expect = [
            'vendor' => ['Apple', 'Samsung', 'Xiaomi'],
            'model' => ['Iphone X Pro Max', 'Galaxy S20', 'Galaxy A5', 'MI 9'],
            'price' => [80999, 70599, 15000, 26000],
            'color' => ['black', 'white', 'yellow'],
            'has_phones' => [1],
            'cam_mp' => [40, 105, 12, 48],
            'sale' => [1, 0]
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

    public function testGetAcceptableFiltersCountNoFilters()
    {
        $records = [
            ['color' => 'black', 'size' => 7, 'group' => 'A'],
            ['color' => 'black', 'size' => 8, 'group' => 'A'],
            ['color' => 'white', 'size' => 7, 'group' => 'B'],
            ['color' => 'yellow', 'size' => 7, 'group' => 'C'],
            ['color' => 'black', 'size' => 7, 'group' => 'C'],
        ];
        $index = $this->loadIndex($records);
        $facets = new Search($index);

        $acceptableFilters = $facets->findAcceptableFiltersCount();

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

    public function testGetAcceptableFiltersCountLimit()
    {
        $records = [
            ['id' => 1, 'color' => 'black', 'size' => 7, 'group' => 'A'],
            ['id' => 2, 'color' => 'black', 'size' => 8, 'group' => 'A'],
            ['id' => 3, 'color' => 'white', 'size' => 7, 'group' => 'B'],
            ['id' => 4, 'color' => 'yellow', 'size' => 7, 'group' => 'C'],
            ['id' => 5, 'color' => 'black', 'size' => 7, 'group' => 'C'],
        ];
        $index = $this->loadIndex($records);
        $facets = new Search($index);

        $acceptableFilters = $facets->findAcceptableFiltersCount([], [1, 2]);

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

    public function testGetAcceptableFiltersCount()
    {
        $records = $this->getTestData();
        $index = $this->loadIndex($records);
        $facets = new Search($index);
        $filter = new ValueFilter('color', 'black');

        $acceptableFilters = $facets->findAcceptableFiltersCount([$filter]);

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

    public function testGetAcceptableFiltersCountMulty()
    {
        $records = [
            ['color' => 'black', 'size' => 7, 'group' => 'A'],
            ['color' => 'black', 'size' => 8, 'group' => 'A'],
            ['color' => 'white', 'size' => 7, 'group' => 'B'],
            ['color' => 'yellow', 'size' => 7, 'group' => 'C'],
            ['color' => 'black', 'size' => 7, 'group' => 'C'],
        ];
        $index = $this->loadIndex($records);
        $facets = new Search($index);
        $filter = new ValueFilter('color', 'black');
        $filter2 = new ValueFilter('size', 7);

        $acceptableFilters = $facets->findAcceptableFiltersCount([$filter, $filter2]);

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

    public function testFindFloat()
    {
        $records = [
            ['id'=>1, 'color' => 'black', 'size' => 7.5, 'group' => 'A'],
            ['id'=>2, 'color' => 'black', 'size' => 8.9, 'group' => 'A'],
            ['id'=>3, 'color' => 'white', 'size' => 7.11, 'group' => 'B'],
        ];
        $index = $this->loadIndex($records);

        $facets = new Search($index);
        $filter = new ValueFilter('size', 7.11);
        $result = $facets->find([$filter]);
        $this->assertEquals(3,$result[0]);
        $filter = new ValueFilter('size', '8.9');
        $result = $facets->find([$filter]);
        $this->assertEquals(2, $result[0]);
        $acceptableFilters = $facets->findAcceptableFiltersCount([$filter]);

        $expect = [
            'color' => ['black' =>1],
            'size' => ['8.9' => 1,'7.5'=>1,'7.11'=>1],
            'group' => ['A' =>1]
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