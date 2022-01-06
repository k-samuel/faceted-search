<?php

use KSamuel\FacetedSearch\Filter\ValueFilter;
use KSamuel\FacetedSearch\Index;
use KSamuel\FacetedSearch\Search;
use KSamuel\FacetedSearch\Sorter\ByField;
use PHPUnit\Framework\TestCase;

class FieldTest extends TestCase
{

    public function testSortDesc()
    {
        $data = [
            1 => ['size' => 12, 'tag' => 1],
            2 => ['size' => 7, 'tag' => 1],
            3 => ['size' => 100, 'tag' => 1],
            4 => ['size' => 8, 'tag' => 1],
            5 => ['size' => 8, 'tag' => 2]
        ];
        $index1 = new Index\ArrayIndex();
        $index2 = new Index\FixedArrayIndex();
        $index2->writeMode();
        foreach ($data as $id => $values) {
            $index1->addRecord($id, $values);
            $index2->addRecord($id, $values);
        }
        $index2->commitChanges();

        $search = new Search($index1);
        $results = $search->find([new ValueFilter('tag', 1)]);
        $sorter = new ByField($index1);
        $sorted = $sorter->sort($results, 'size', ByField::SORT_DESC);
        $this->assertEquals([3, 1, 4, 2], $sorted);

        $search = new Search($index2);
        $results = $search->find([new ValueFilter('tag', 1)]);
        $sorter = new ByField($index2);
        $sorted = $sorter->sort($results, 'size', ByField::SORT_DESC);
        $this->assertEquals([3, 1, 4, 2], $sorted);
    }

    public function testSortAsc()
    {
        $data = [
            1 => ['size' => 12, 'tag' => 1],
            2 => ['size' => 7, 'tag' => 1],
            3 => ['size' => 100, 'tag' => 1],
            4 => ['size' => 8, 'tag' => 1],
            5 => ['size' => 8, 'tag' => 2]
        ];
        $index1 = new Index\ArrayIndex();
        $index2 = new Index\FixedArrayIndex();
        $index2->writeMode();
        foreach ($data as $id => $values) {
            $index1->addRecord($id, $values);
            $index2->addRecord($id, $values);
        }
        $index2->commitChanges();

        $search = new Search($index1);
        $results = $search->find([new ValueFilter('tag', 1)]);
        $sorter = new ByField($index1);
        $sorted = $sorter->sort($results, 'size', ByField::SORT_ASC);
        $this->assertEquals([2, 4, 1, 3], $sorted);

        $search = new Search($index2);
        $results = $search->find([new ValueFilter('tag', 1)]);
        $sorter = new ByField($index2);
        $sorted = $sorter->sort($results, 'size', ByField::SORT_ASC);
        $this->assertEquals([2, 4, 1, 3], $sorted);
    }
}