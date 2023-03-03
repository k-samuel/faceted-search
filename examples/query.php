<?php

include '../vendor/autoload.php';

use KSamuel\FacetedSearch\Search;
use KSamuel\FacetedSearch\Filter\FilterInterface;
use KSamuel\FacetedSearch\Filter\ValueFilter;
use KSamuel\FacetedSearch\Index\Factory;
use KSamuel\FacetedSearch\Index\IndexInterface;
use KSamuel\FacetedSearch\Query\AggregationQuery;
use KSamuel\FacetedSearch\Query\SearchQuery;

/**
 * Find acceptable filters
 * @param Search $search
 * @param array<FilterInterface> $filters
 * @return array
 */
function findFilters(IndexInterface $search, array $filters): array
{
    $query = (new AggregationQuery())->filters($filters)->countItems();
    $data =  $search->aggregate($query);

    ksort($data);
    foreach ($data as &$filterValues) {
        ksort($filterValues);
    }
    unset($filterValues);
    return ['data' => $data];
}

/**
 * Find products using filters
 * @param IndexInterface $search
 * @param array<FilterInterface> $filters
 * @param string $index
 * @param int $pageLimit
 * @return array
 */
function findProducts(IndexInterface $search, array $filters, string $index, int $pageLimit): array
{
    // find product id
    $data = $search->query((new SearchQuery())->filters($filters));
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
$index = 'oils';
$pageLimit = 20;
if ($_GET['cat'] === 'shoe') {
    $index = 'shoe';
}
$filters = [];
if (isset($_POST['filters'])) {
    $filtersData = json_decode($_POST['filters'], true);
    foreach ($filtersData as $name => $values) {
        $filters[] = new ValueFilter($name, $values);
    }
}

// Load index by product category
// Use database to store index at your production
$indexData = json_decode(file_get_contents('./data/' . $index . '-index.json'), true);
$search = (new Factory)->create(Factory::ARRAY_STORAGE);
$search->setData($indexData);

$result = [
    'filters' => findFilters($search, $filters),
    'results' => findProducts($search, $filters, $index, $pageLimit),
];
echo json_encode($result);
