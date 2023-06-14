<?php

require '../../vendor/autoload.php';

use KSamuel\FacetedSearch\Filter\FilterInterface;
use KSamuel\FacetedSearch\Filter\ExcludeValueFilter;
use KSamuel\FacetedSearch\Filter\RangeFilter;
use KSamuel\FacetedSearch\Filter\ValueFilter;

use KSamuel\FacetedSearch\Index\IndexInterface;
use KSamuel\FacetedSearch\Index\Factory;
use KSamuel\FacetedSearch\Index\Profile;
use KSamuel\FacetedSearch\Query\AggregationQuery;
use KSamuel\FacetedSearch\Query\Order;

use KSamuel\FacetedSearch\Query\SearchQuery;


$dataFile = './facet.json';

gc_collect_cycles();
$t = microtime(true);
$m = memory_get_usage();
$indexData = json_decode(file_get_contents($dataFile), true);
$time = (microtime(true) - $t);

$search = (new Factory)->create(Factory::ARRAY_STORAGE);
$profile = new Profile;
$search->setProfiler($profile);

$search->setData($indexData);
unset($indexData);
gc_collect_cycles();
$memUse = (int)((memory_get_usage() - $m) / 1024 / 1024);
$resultData[] = ['Index memory usage', (string) $memUse . "Mb", ''];
$resultData[] = ['Loading time', number_format($time, 6) . 's', ''];

$t = microtime(true);
$search->optimize();
$time = microtime(true) - $t;
$resultData[] = ['Optimize time', number_format($time, 6) . 's', ''];

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

$filters3 = [
    new ValueFilter('color', 'black'),
    new ValueFilter('warehouse', [789, 45, 65, 1, 10]),
    new ExcludeValueFilter('type', ['good'])
];

/**
 * Find test
 * @param IndexInterface $search
 * @param array<FilterInterface> $filters
 * @return array<int,int|string>
 */
function find(IndexInterface $search, array $filters): array
{
    $t = microtime(true);
    $results = $search->query((new SearchQuery())->filters($filters));
    $time = microtime(true) - $t;
    return ['Find', number_format($time, 6) . "s", count($results)];
}

/**
 * Find & sort test 
 * @param IndexInterface $search
 * @param array<FilterInterface> $filters
 * @return array<int,int|string>
 */
function findAndSort(IndexInterface $search, array $filters): array
{
    $t = microtime(true);
    $results = $search->query((new SearchQuery())->filters($filters)->order('quantity', Order::SORT_DESC));
    $time = microtime(true) - $t;
    return ['Find & Sort', number_format($time, 6) . "s", count($results)];
}

/**
 * Aggregate test
 * @param IndexInterface $search
 * @param array<FilterInterface> $filters
 * @return array<int,int|string>
 */
function aggregate(IndexInterface $search, array $filters): array
{
    $t = microtime(true);
    $filtersData = $search->aggregate((new AggregationQuery())->filters($filters));
    $time = microtime(true) - $t;
    return ['Filters', number_format($time, 6) . "s", count($filters)];
}

/**
 * Aggregate & count test
 * @param IndexInterface $search
 * @param array<FilterInterface> $filters
 * @return array<int,int|string>
 */
function aggregateAndCount(IndexInterface $search, array $filters): array
{
    $query = (new AggregationQuery())
        ->filters($filters)
        ->countItems(true);

    $t = microtime(true);
    $filtersData = $search->aggregate($query);
    $time = microtime(true) - $t;
    return ['Filters & count', number_format($time, 6) . "s", count($filters)];
}

/**
 * Aggregate & count test
 * @param IndexInterface $search
 * @param array<FilterInterface> $filters
 * @return array<int,int|string>
 */
function aggregateAndCountWithExclude(IndexInterface $search, array $filters): array
{
    $query = (new AggregationQuery())
        ->filters($filters)
        ->countItems(true);

    $t = microtime(true);
    $filtersData = $search->aggregate($query);
    $time = microtime(true) - $t;
    return ['Filters & count & exc', number_format($time, 6) . "s", count($filters)];
}



/**
 * Sort test
 * @param IndexInterface $search
 * @param array<FilterInterface> $filters
 * @param Profile $profile
 * @return array<int,int|string>
 */
function sortTest(IndexInterface $search, array $filters, Profile $profile): array
{
    $results = $search->query((new SearchQuery())->filters($filters)->order('quantity', Order::SORT_DESC));
    return ['Sort', number_format($profile->getSortingTime(), 6) . "s", count($filters)];
}


/**
 * Find with rage test (other filters)
 * @param IndexInterface $search
 * @param array<FilterInterface> $filters
 * @return array<int,int|string>
 */
function findWithRange(IndexInterface $search, array $filters): array
{
    $t = microtime(true);
    $results2 = $search->query((new SearchQuery())->filters($filters));
    $time = microtime(true) - $t;
    return ['Find (ranges)', number_format($time, 6) . "s", count($results2)];
}

/**
 * Find with exclude filters test (other filters)
 * @param IndexInterface $search
 * @param array<FilterInterface> $filters
 * @return array<int,int|string>
 */
function findWithExclude(IndexInterface $search, array $filters): array
{
    $t = microtime(true);
    $results2 = $search->query((new SearchQuery())->filters($filters));
    $time = microtime(true) - $t;
    return ['Find (unsets)', number_format($time, 6) . "s", count($results2)];
}


$resultData[] = find($search, $filters);
$resultData[] = findAndSort($search, $filters);
$resultData[] = findWithExclude($search, $filters3);
$resultData[] = findWithRange($search, $filters2);
$resultData[] = aggregate($search, $filters);
$resultData[] = aggregateAndCount($search, $filters);
$resultData[] = aggregateAndCountWithExclude($search, $filters3);
$resultData[] = sortTest($search, $filters, $profile);








$count = $search->getCount();
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
