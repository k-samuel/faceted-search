<?php

include '../vendor/autoload.php';

use KSamuel\FacetedSearch\Search;
use KSamuel\FacetedSearch\Filter\FilterInterface;
use KSamuel\FacetedSearch\Filter\ValueFilter;
use KSamuel\FacetedSearch\Filter\ExcludeValueFilter;
use KSamuel\FacetedSearch\Filter\RangeFilter;
use KSamuel\FacetedSearch\Index\Factory;
use KSamuel\FacetedSearch\Index\IndexInterface;
use KSamuel\FacetedSearch\Query\AggregationQuery;
use KSamuel\FacetedSearch\Query\AggregationSort;
use KSamuel\FacetedSearch\Query\Order;
use KSamuel\FacetedSearch\Query\SearchQuery;

/**
 * Find acceptable filters
 * @param Search $search
 * @param array<FilterInterface> $filters
 * @return array<string,mixed>
 */
function findFilters(IndexInterface $search, array $filters, array $filterList): array
{
    $query = (new AggregationQuery())->filters($filters)->countItems()->sort();
    $data =  $search->aggregate($query);
    $result = [];

    if (!empty($filterList)) {
        foreach ($filterList as $key) {
            if (!isset($data[$key])) {
                continue;
            }
            $result[$key] = $data[$key];
        }
    } else {
        $result = $data;
    }

    return [
        'data' => $result,
        'price_step' => 200, // step of RangeIndexer for mobile
    ];
}

/**
 * Find products using filters
 * @param IndexInterface $search
 * @param array<FilterInterface> $filters
 * @param int $pageLimit
 * @param string $order  - sort by field
 * @param string $dir  - sort direction
 * @return array<string,mixed>
 */
function findProducts(IndexInterface $search, array $filters, int $pageLimit, string $order, string $dir, string $index): array
{
    $query = (new SearchQuery())->filters($filters);
    if (!empty($order)) {
        if ($dir === 'desc') {
            $dir = Order::SORT_DESC;
        } else {
            $dir = Order::SORT_ASC;
        }
        $query->order($order, $dir);
    }

    // find product id
    $data = $search->query($query);
    $resultItems = [];
    $count = count($data);
    if (!empty($data)) {
        $data = array_chunk($data, $pageLimit);
        $data = $data[0];
        // get product info from DB
        $db = include './data/' . $index . '-db.php';
        foreach ($data as $id) {
            $resultItems[] = $db[$id];
        }
    }
    return ['data' => $resultItems, 'count' => $count, 'limit' => $pageLimit];
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");


// Note it's just simplified example. Filter input using your framework
$filters = [];
$pageLimit = 20;
if (isset($_POST['filters'])) {
    $filtersData = json_decode($_POST['filters'], true);
    if (isset($filtersData['include'])) {
        foreach ($filtersData['include'] as $name => $values) {
            $filters[] = new ValueFilter($name, array_keys($values));
        }
    }
    if (isset($filtersData['exclude'])) {
        foreach ($filtersData['exclude'] as $name => $values) {
            $filters[] = new ExcludeValueFilter($name, array_keys($values));
        }
    }
}
$dir = 'asc';
$order = '';
if (isset($_POST['order'])) {
    $order = $_POST['order'];
}
if (isset($_POST['dir']) && $_POST['dir'] === 'desc') {
    $dir = 'desc';
}

// Processing custom filter for price
$priceFrom = 0;
$priceTo = 0;
$priceRange = [];

if (!empty($_POST['price_from'])) {
    $priceRange['min'] = intval($_POST['price_from']);;
}
if (!empty($_POST['price_to'])) {
    $priceRange['max'] = intval($_POST['price_to']);
}
if (!empty($priceRange)) {
    $filters[] = new RangeFilter('price', $priceRange);
}

$acceptedIndexes = ['mobile'];
if (in_array($_GET['cat'] ?: '', $acceptedIndexes, true)) {
    $index = $_GET['cat'];
} else {
    $index = 'oils';
}

// Load index by product category
// Use database to store index at your production
$indexData = json_decode(file_get_contents('./data/' . $index . '-index.json'), true);
$search = (new Factory)->create(Factory::ARRAY_STORAGE);
$search->setData($indexData);

$titles = [
    'brand' => 'Brand',
    'price_range' => 'Price Range',
    'hd' => 'Memory Storage, Gb',
    'state' => 'Quality',
    'color' => 'Color',
    'diagonal' => 'Size',
    'battery' => 'Battery',
    'cam' => 'Cam resolution, MP',
    'ram' => 'Memory RAM',
];

$result = [
    'filters' => findFilters($search, $filters, array_keys($titles)),
    'results' => findProducts($search, $filters, $pageLimit, $order, $dir, $index),
    'titles' => $titles
];
echo json_encode($result);
