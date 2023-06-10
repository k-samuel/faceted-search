<?php

use PHPUnit\Framework\TestCase;
use KSamuel\FacetedSearch\Filter\ValueFilter;
use KSamuel\FacetedSearch\Index\ArrayIndex;
use KSamuel\FacetedSearch\Index\Factory;
use KSamuel\FacetedSearch\Index\IndexInterface;
use KSamuel\FacetedSearch\Index\Storage\FixedArrayStorage;
use KSamuel\FacetedSearch\Query\AggregationQuery;
use KSamuel\FacetedSearch\Query\SearchQuery;
use KSamuel\FacetedSearch\Search;

class ValueFilterTest extends TestCase
{

    public function testSetValue(): void
    {
        $this->setValueTest((new Factory)->create(Factory::ARRAY_STORAGE));
        $this->setValueTest((new Factory)->create(Factory::FIXED_ARRAY_STORAGE));
    }

    private function setValueTest(IndexInterface $index): void
    {
        $records = [
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

        $storage = $index->getStorage();

        foreach ($records as $id => $item) {
            $storage->addRecord($id, $item);
        }
        $storage->optimize();

        $filter = new ValueFilter('vendor');
        $filter->setValue(['Test']);
        $filter2 = new ValueFilter('color');
        $filter2->setValue(['white']);
        $result = $index->query((new SearchQuery)->filters([$filter, $filter2]));
        $this->assertEmpty($result);
        $result = $index->aggregate((new AggregationQuery)->filters([$filter, $filter2]));
        $this->assertEquals($result, ['vendor' => ['Apple' => true]]);
    }
}
