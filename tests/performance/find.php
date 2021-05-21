<?php

require '../../vendor/autoload.php';

use KSamuel\FacetedSearch\Filter\RangeFilter;
use KSamuel\FacetedSearch\Filter\ValueFilter;
use KSamuel\FacetedSearch\Index;
use KSamuel\FacetedSearch\Search;
use KSamuel\FacetedSearch\Sorter\ByField;

$t = microtime(true);
$m = memory_get_usage();
$indexData = json_decode(file_get_contents('./facet.json'), true);
echo "index memory usage: " . (string)(int)((memory_get_usage() - $m) / 1024 / 1024) . "Mb" . PHP_EOL;
echo 'loading: ' . (microtime(true) - $t) . "s" . PHP_EOL;

$t = microtime(true);
$index = new Index();
$index->setData($indexData);

$search = new Search($index);

$filters = [
    new ValueFilter('color', 'black'),
    new ValueFilter('warehouse', [789, 45, 65, 1, 10]),
    new RangeFilter('price', ['min' => 1000, 'max' => 7000])
];

/// test find
$results = $search->find($filters);
echo 'Results: ' . count($results) . "\t\t" . (microtime(true) - $t) . "s" . PHP_EOL;

// test sort
$sorter = new ByField($index);
$sortField = 'quantity';
$t = microtime(true);
$results = $sorter->sort($results, $sortField, ByField::SORT_DESC);
echo 'Sort by quantity DESC: '. "\t" . (microtime(true) - $t) . "s" . PHP_EOL;

// uncoment to verify results
/*
$data = \json_decode(file_get_contents('./data.json'), true);
foreach ($results as $id) {
    echo $id . ' : ' . $data[$id][$sortField] . PHP_EOL;
}
*/

//test acceptable
$t = microtime(true);
$filtersData = $search->findAcceptableFilters($filters);
echo 'Filters: ' . count($filters) . "\t\t" . (microtime(true) - $t) . "s" . PHP_EOL;

//test acceptable with count
$t = microtime(true);
$filtersData = $search->findAcceptableFiltersCount($filters);
echo 'Filters with count: ' . count($filters) . "\t" . (microtime(true) - $t) . "s" . PHP_EOL;
