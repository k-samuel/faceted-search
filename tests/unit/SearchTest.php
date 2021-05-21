<?php
use PHPUnit\Framework\TestCase;
use KSamuel\FacetedSearch\Search;
use KSamuel\FacetedSearch\Filter\ValueFilter;
use KSamuel\FacetedSearch\Filter\RangeFilter;
use KSamuel\FacetedSearch\Index;

class SearchTest extends TestCase
{

    public function testFind()
    {
        $records = $this->getTestData();
        $index = new Index();

        foreach ($records as $id=>$item){
            $index->addRecord($id, $item);
        }
        $facets = new Search($index);
        $filter = new ValueFilter('vendor');
        $filter->setValue(['Samsung','Apple']);

        $filter2 = new RangeFilter('cam_mp');
        $filter2->setValue(['min'=>16]);

        $filter3 = new RangeFilter('price');
        $filter3->setValue(['max'=>80000]);

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
        $this->assertEquals([3,4], $result);

        $result = $facets->find($filters, array_keys($records));
        sort($result);
        $this->assertEquals([3,4], $result);

        $filter = new ValueFilter('vendor');
        $filter->setValue(['Google']);
        $result = $facets->find([$filter]);
        $this->assertEquals([], $result);

        $index->setData($index->getData());
        $filter = new ValueFilter('vendor_field');
        $filter->setValue(['Google']);
        $result = $facets->find([$filter],[3,4]);
        $this->assertEquals([], $result);
    }

    public function testGetAcceptableFilters()
    {
        $records = $this->getTestData();
        $index = new Index();
        foreach ($records as $id=>$item){
            $index->addRecord($id, $item);
        }
        $facets = new Search($index);
        $filter = new ValueFilter('color' ,'black');

        $acceptableFilters = $facets->findAcceptableFilters([$filter]);

        $expect = [
            'vendor' => ['Apple','Samsung','Xiaomi'],
            'model' => ['Iphone X Pro Max','Galaxy S20', 'Galaxy A5', 'MI 9'],
            'price' => [80999, 70599, 15000, 26000],
            'color' => ['black','white','yellow'],
            'has_phones' => [1],
            'cam_mp' => [40, 105, 12, 48],
            'sale' => [1,0]
        ];
        foreach($expect as $field=>&$values){
            sort($values);
        }unset($values);
        foreach($acceptableFilters as $field=>&$values){
            sort($values);
        }unset($values);

        foreach ($expect as $filter => $values){
            $this->assertArrayHasKey($filter, $acceptableFilters);
            $this->assertEquals($values, $acceptableFilters[$filter]);
        }
    }

    public function  testGetAcceptableFiltersCount()
    {
        $records = $this->getTestData();
        $index = new Index();
        foreach ($records as $id=>$item){
            $index->addRecord($id, $item);
        }
        $facets = new Search($index);
        $filter = new ValueFilter('color' ,'black');

        $acceptableFilters = $facets->findAcceptableFiltersCount([$filter]);

        $expect = [
            'vendor' => ['Apple'=>1,'Samsung'=>2,'Xiaomi'=>1],
            'model' => ['Iphone X Pro Max'=>1,'Galaxy S20'=>1, 'Galaxy A5'=>1, 'MI 9'=>1],
            'price' => [80999=>1, 70599=>1, 15000=>1, 26000=>1],
            // self filtering is not using by facets logic
            'color' => ['black'=>4,'white'=>1,'yellow'=>1],
            'has_phones' => [1=>4],
            'cam_mp' => [40=>1, 105=>1, 12=>1, 48=>1],
            'sale' => [1=>3,0=>1]
        ];
        foreach($expect as $field=>&$values){
            sort($values);
        }unset($values);
        foreach($acceptableFilters as $field=>&$values){
            sort($values);
        }unset($values);

        foreach ($expect as $filter => $values){
            $this->assertArrayHasKey($filter, $acceptableFilters);
            $this->assertEquals($values, $acceptableFilters[$filter]);
        }
    }

    public function getTestData() : array
    {
        return [
            1=> [
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