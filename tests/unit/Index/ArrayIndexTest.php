<?php

use KSamuel\FacetedSearch\Filter\ValueFilter;
use KSamuel\FacetedSearch\Index\ArrayIndex;
use KSamuel\FacetedSearch\Query\SearchQuery;
use PHPUnit\Framework\TestCase;

class ArrayIndexTest extends TestCase
{
    public function testDeleteRecord(): void
    {
        $testData = [
            10 => [
                'size' => 100,
                'color' => ['red', 'green'],
                'status' => 'new',
                'grade' => 'first',
                'warehouse' => [1001, 1002]
            ],
            11 => [
                'size' => 200,
                'color' => 'black',
                'status' => 'used'
            ],
            12 => [
                'size' => 120,
                'color' => 'green',
                'status' => 'new'
            ]
        ];
        $index = new ArrayIndex();
        foreach ($testData as $id => $values) {
            $index->addRecord($id, $values);
        }

        $result = $index->query(
            (new SearchQuery())->filter(new ValueFilter('color', 'red'))
        );
        $this->assertEquals([10], $result);


        $index->deleteRecord(10);
        $result = $index->query(
            (new SearchQuery())->filter(new ValueFilter('color', 'red'))
        );
        $this->assertEquals([], $result);
    }

    public function testUpdateRecord(): void
    {
        $testData = [
            10 => [
                'size' => 100,
                'color' => ['red', 'green'],
                'status' => 'new',
                'grade' => 'first',
                'warehouse' => [1001, 1002]
            ],
            11 => [
                'size' => 200,
                'color' => 'blue',
                'status' => 'used'
            ],
            12 => [
                'size' => 120,
                'color' => 'green',
                'status' => 'new'
            ]
        ];
        $index = new ArrayIndex();
        foreach ($testData as $id => $values) {
            $index->addRecord($id, $values);
        }

        $index->replaceRecord(10, [
            'size' => 150,
            'color' => ['green', 'blue'],
            'status' => 'new',
            'sellerId' => 120
        ]);

        $result = $index->query(
            (new SearchQuery())->filter(new ValueFilter('color', 'red'))
        );
        $this->assertEquals([], $result);

        $result = $index->query(
            (new SearchQuery())->filter(new ValueFilter('sellerId', 120))
        );
        $this->assertEquals([10], $result);

        $result = $index->query(
            (new SearchQuery())->filters([
                new ValueFilter('color', 'blue'),
                new ValueFilter('size', 150),
            ])
        );
        $this->assertEquals([10], $result);
    }
}
