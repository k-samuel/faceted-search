<?php

use KSamuel\FacetedSearch\Filter\ValueFilter;
use KSamuel\FacetedSearch\Index;
use KSamuel\FacetedSearch\Search;
use KSamuel\FacetedSearch\Sorter\ByField;
use PHPUnit\Framework\TestCase;

class FieldTest extends TestCase
{

    public function testSort()
    {
        $data = [
            1 => ['size' => 12, 'tag' => 1],
            2 => ['size' => 7, 'tag' => 1],
            3 => ['size' => 100, 'tag' => 1],
            4 => ['size' => 8, 'tag' => 1],
            5 => ['size' => 8, 'tag' => 2]
        ];
        $index = new Index();
        foreach ($data as $id => $values) {
            $index->addRecord($id, $values);
        }
        $search = new Search($index);
        $results = $search->find([new ValueFilter('tag', 1)]);

        $sorter = new ByField($index);
        $sorted = $sorter->sort($results, 'size', ByField::SORT_DESC);
        $this->assertEquals([3, 1, 4, 2], $sorted);
    }
}