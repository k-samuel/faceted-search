<?php

include '../vendor/autoload.php';

use KSamuel\FacetedSearch\Search;
use KSamuel\FacetedSearch\Filter\FilterInterface;
use KSamuel\FacetedSearch\Filter\ValueFilter;
use KSamuel\FacetedSearch\Filter\ExcludeValueFilter;
use KSamuel\FacetedSearch\Index\Factory;
use KSamuel\FacetedSearch\Index\IndexInterface;
use KSamuel\FacetedSearch\Query\AggregationQuery;
use KSamuel\FacetedSearch\Query\AggregationSort;
use KSamuel\FacetedSearch\Query\SearchQuery;

/**
 * Find acceptable filters
 * @param Search $search
 * @param array<FilterInterface> $filters
 * @return array
 */
function findFilters(IndexInterface $search, array $filters): array
{
    $query = (new AggregationQuery())->filters($filters)->countItems()->Sort(AggregationSort::SORT_ASC);
    $data =  $search->aggregate($query);

    return [
        'data' => $data,
        'price_step' => 200 // stp of RangeIndexer
    ];
}

/**
 * Find products using filters
 * @param IndexInterface $search
 * @param array<FilterInterface> $filters
 * @param int $pageLimit
 * @return array
 */
function findProducts(IndexInterface $search, array $filters, int $pageLimit): array
{
    // find product id
    $data = $search->query((new SearchQuery())->filters($filters));
    $resultItems = [];
    $count = count($data);
    if (!empty($data)) {
        $data = array_chunk($data, $pageLimit);
        $data = $data[0];
        // get product info from DB
        $db = include './data/mobile-db.php';
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

// Load index by product category
// Use database to store index at your production
$indexData = json_decode(file_get_contents('./data/mobile-index.json'), true);
$search = (new Factory)->create(Factory::ARRAY_STORAGE);
$search->setData($indexData);

$result = [
    'filters' => findFilters($search, $filters),
    'results' => findProducts($search, $filters, $pageLimit),
];
echo json_encode($result);
