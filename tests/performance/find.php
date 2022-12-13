<?php

require '../../vendor/autoload.php';

use KSamuel\FacetedSearch\Filter\RangeFilter;
use KSamuel\FacetedSearch\Filter\ValueFilter;
use KSamuel\FacetedSearch\Index;
use KSamuel\FacetedSearch\Query\AggregationQuery;
use KSamuel\FacetedSearch\Search;
use KSamuel\FacetedSearch\Query\SearchQuery;
use KSamuel\FacetedSearch\Sorter\ByField;


$dataFile = './facet.json';

gc_collect_cycles();
$t = microtime(true);
$m = memory_get_usage();
$indexData = json_decode(file_get_contents($dataFile), true);
$time = (microtime(true) - $t);

$index = new Index\ArrayIndex();
//$index = new Index\FixedArrayIndex();

$index->setData($indexData);
unset($indexData);
gc_collect_cycles();
$memUse = (int)((memory_get_usage() - $m) / 1024 / 1024);
$resultData[] = ['Index memory usage', (string) $memUse . "Mb", ''];
$resultData[] = ['Loading time', number_format($time, 6) . 's', ''];

$t = microtime(true);
//$index->writeMode();
$index->optimize();
//$index->commitChanges();
$time = microtime(true) - $t;
$resultData[] = ['Optimize time', number_format($time, 6) . 's', ''];

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

//test find
$t = microtime(true);
$results = $search->query((new SearchQuery())->filters($filters));
$time = microtime(true) - $t;
$resultData[] = ['Find Results', number_format($time, 6) . "s", count($results)];

//test acceptable
$t = microtime(true);
$filtersData = $search->aggregate((new AggregationQuery())->filters($filters));
$time = microtime(true) - $t;
$resultData[] = ['Filters', number_format($time, 6) . "s", count($filters)];

//test aggregate with count
$query = (new AggregationQuery())
    ->filters($filters)
    ->countItems(true);

$t = microtime(true);
$filtersData = $search->aggregate($query);
$time = microtime(true) - $t;
$resultData[] = ['Filters with count', number_format($time, 6) . "s", count($filters)];



/*
// find and sorter
$sorter = new ByField($index);
$sortField = 'quantity';
$t = microtime(true);
$results = $search->find($filters);
$results = $sorter->sort($results, $sortField, ByField::SORT_DESC);
$time = microtime(true) - $t;
$resultData[] = ['Find + Sorter', number_format($time, 6) . "s", count($filters)];

// SearchQuery & sort
$query = (new SearchQuery())
    ->filter(new ValueFilter('color', 'black'))
    ->filter(new ValueFilter('warehouse', [789, 45, 65, 1, 10]))
    ->filter(new ValueFilter('type', ["normal", "middle"]))
    ->order($sortField, ByField::SORT_DESC);

$t = microtime(true);
$results = $search->query($query);
$time = microtime(true) - $t;
$resultData[] = ['Query + Sort', number_format($time, 6) . "s", count($filters)];

/// test find with Range
$t = microtime(true);
$results2 = $search->query((new SearchQuery())->filters($filters2));
$time = microtime(true) - $t;
$resultData[] = ['Find Results (ranges)', number_format($time, 6) . "s", count($results2)];

*/

//test sort
$sorter = new ByField($index);
$sortField = 'quantity';
$results = $search->query((new SearchQuery())->filters($filters));
$t = microtime(true);
$results = $sorter->sort($results, $sortField, ByField::SORT_DESC);
$time = microtime(true) - $t;
$resultData[] = ['Sort by quantity DESC', number_format($time, 6) . "s", count($results)];


$count = count($index->getAllRecordIdMap());
$index->resetLocalCache();
array_unshift($resultData, ['Records', number_format($count), '']);


$colLen = [25, 14, 10];
echo str_repeat("-", 56) . PHP_EOL;

foreach ($resultData as $index => $cols) {
    if ($index == 4) {
        echo str_repeat("-", 56) . PHP_EOL;
    }

    foreach ($cols as $id => $item) {
        echo '| ' . str_pad(' ' . $item, $colLen[$id]);
    }
    echo "|" . PHP_EOL;
}
echo str_repeat("-", 56) . PHP_EOL;
