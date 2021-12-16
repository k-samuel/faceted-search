<?php

require '../../vendor/autoload.php';

use KSamuel\FacetedSearch\Filter\RangeFilter;
use KSamuel\FacetedSearch\Filter\ValueFilter;
use KSamuel\FacetedSearch\Index;
use KSamuel\FacetedSearch\Search;
use KSamuel\FacetedSearch\Sorter\ByField;

$t = microtime(true);
$m = memory_get_usage();
$indexData = json_decode(file_get_contents('./1000000/ifacet.json'), true);
$time = (microtime(true) - $t);
$memUse = (int)((memory_get_usage() - $m) / 1024 / 1024);

$index = new Index();
$index->setData($indexData);
$resultData[] = ['Index memory usage', (string) $memUse. "Mb",''];
$resultData[] = ['Loading time', number_format($time,6) . 's', ''];

$search = new Search($index);

$filters = [
    new ValueFilter('color', 'black'),
    new ValueFilter('warehouse', [789, 45, 65, 1, 10]),
    new ValueFilter('type', ["normal", "middle"]),
];

$filters2 = [
    new ValueFilter('color', 'black'),
    new ValueFilter('warehouse', [789, 45, 65, 1, 10]),
    new RangeFilter('price', ['min' => 1000, 'max' => 5000])
];


/// test find
$t = microtime(true);
$results = $search->find($filters);
$time = microtime(true) - $t;
$resultData[] = ['Find Results', number_format($time,6) . "s", count($results)];

/// test find with Range
$t = microtime(true);
$results2 = $search->find($filters2);
$time = microtime(true) - $t;
$resultData[] = ['Find Results (ranges)', number_format($time,6) . "s", count($results2)];

// test sort
$sorter = new ByField($index);
$sortField = 'quantity';
$t = microtime(true);
$results = $sorter->sort($results, $sortField, ByField::SORT_DESC);
$time = microtime(true) - $t;
$resultData[] = ['Sort by quantity DESC', number_format($time,6) . "s", count($results)];

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
$time = microtime(true) - $t;
$resultData[] = ['Filters', number_format($time,6) . "s", count($filters)];

//test acceptable with count
$t = microtime(true);
$filtersData = $search->findAcceptableFiltersCount($filters);
$time = microtime(true) - $t;
$resultData[] = ['Filters with count', number_format($time,6) . "s", count($filters)];


$colLen = [25, 14, 10];
echo str_repeat("-", 56) . PHP_EOL;
foreach ($resultData as $index => $cols) {
    if ($index === 2) {
        echo str_repeat("-", 56) . PHP_EOL;
    }

    foreach ($cols as $id => $item) {
        echo '| ' . str_pad(' ' . $item, $colLen[$id]);
    }
    echo "|" . PHP_EOL;
}
echo str_repeat("-", 56) . PHP_EOL;
