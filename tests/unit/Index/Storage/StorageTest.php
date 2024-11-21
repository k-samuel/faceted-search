<?php

use KSamuel\FacetedSearch\Filter\ValueFilter;
use KSamuel\FacetedSearch\Index\Factory;
use KSamuel\FacetedSearch\Index\IndexInterface;
use KSamuel\FacetedSearch\Query\SearchQuery;
use PHPUnit\Framework\TestCase;

class StorageTest extends TestCase
{
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
    public function testAddRecord(IndexInterface $index): void
    {
        $storage = $index->getStorage();

        $records = [
            112 => ['vendor' => 'Tester', 'price' => 100],
            113 => ['vendor' => 'Tester2', 'price' => 101],
            114 => ['vendor' => 'Tester2', 'price' => 101]
        ];

        foreach ($records as $id => $val) {
            $this->assertTrue($storage->addRecord($id, $val));
            if ($id === 112) {
                $storage->optimize();
            }
        }

        $expected = [
            'vendor' => [
                'Tester' => [112],
                'Tester2' => [113, 114]
            ],
            'price' => [
                100 => [112],
                101 => [113, 114]
            ]
        ];

        $this->assertEquals($expected, $storage->getData());
    }
    /**
     * @dataProvider storeProvider
     */
    public function testHasField(IndexInterface $index): void
    {
        $storage = $index->getStorage();
        $records = [
            112 => ['vendor' => 'Tester', 'price' => 100],
            113 => ['vendor' => 'Tester2', 'price' => 101],
            114 => ['vendor' => 'Tester2', 'price' => 101]
        ];

        foreach ($records as $id => $val) {
            $this->assertTrue($storage->addRecord($id, $val));
        }
        $storage->optimize();

        $storage->optimize();

        $this->assertTrue($storage->hasField('vendor'));
        $this->assertFalse($storage->hasField('undefined_field'));
    }
    /**
     * @dataProvider storeProvider
     */
    public function testExport(IndexInterface $index): void
    {
        $storage = $index->getStorage();

        $records = [
            112 => ['vendor' => 'Tester', 'price' => 100],
            113 => ['vendor' => 'Tester2', 'price' => 101]
        ];

        foreach ($records as $id => $val) {
            $this->assertTrue($storage->addRecord($id, $val));
        }
        $storage->optimize();

        $expected = [
            'vendor' => [
                'Tester' => [112],
                'Tester2' => [113],
            ],
            'price' => [
                100 => [112],
                101 => [113]
            ]
        ];


        $this->assertEquals($expected, $storage->export());
    }
    /**
     * @dataProvider storeProvider
     */
    public function testRecordsCount(IndexInterface $index): void
    {
        $storage = $index->getStorage();

        $records = [
            112 => ['vendor' => 'Tester', 'price' => 100],
            113 => ['vendor' => 'Tester2', 'price' => 101],
            114 => ['vendor' => 'Tester2', 'price' => 101]
        ];

        foreach ($records as $id => $val) {
            $this->assertTrue($storage->addRecord($id, $val));
        }
        $storage->optimize();

        $this->assertEquals(2, $storage->getRecordsCount('price', 101));
        $this->assertEquals(0, $storage->getRecordsCount('price', 500));
        $this->assertEquals(0, $storage->getRecordsCount('price2', 500));
    }

    /**
     * @dataProvider storeProvider
     */
    public function testSetData(IndexInterface $index): void
    {
        $storage = $index->getStorage();
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

        $storage->setData($data);
        $this->assertEquals($data, $storage->export());
    }

    /**
     * @dataProvider storeProvider
     */
    public function testOptimize(IndexInterface $index): void
    {
        $storage = $index->getStorage();
        $data = [
            'field1' => [
                'val1' => [3, 1, 2],
                'val2' => [2, 4]
            ],
            'field2' => [
                'val1' => [1],
                'val2' => [3, 4]
            ]
        ];

        $storage->setData($data);
        $storage->optimize();

        $expect = [
            'field1' => [
                'val2' => [2, 4],
                'val1' => [1, 2, 3],

            ],
            'field2' => [
                'val1' => [1],
                'val2' => [3, 4]
            ]
        ];
        $this->assertEquals($expect, $storage->export());
    }

    /**
     * @dataProvider storeProvider
     */
    public function testDeleteRecord(IndexInterface $index): void
    {
        $storage = $index->getStorage();
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

        foreach ($testData as $id => $values) {
            $storage->addRecord($id, $values);
        }
        $storage->optimize();

        $result = $index->query(
            (new SearchQuery())->filter(new ValueFilter('color', 'red'))
        );
        $this->assertEquals([10], $result);


        $storage->deleteRecord(10);
        $result = $index->query(
            (new SearchQuery())->filter(new ValueFilter('color', 'red'))
        );
        $this->assertEquals([], $result);
    }
    /**
     * @dataProvider storeProvider
     */
    public function testUpdateRecord(IndexInterface $index): void
    {
        $storage = $index->getStorage();
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

        foreach ($testData as $id => $values) {
            $storage->addRecord($id, $values);
        }
        $storage->optimize();

        $storage->replaceRecord(10, [
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
