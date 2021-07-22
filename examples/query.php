<?php

include '../vendor/autoload.php';

use KSamuel\FacetedSearch\Index;
use KSamuel\FacetedSearch\Search;
use KSamuel\FacetedSearch\Filter\ValueFilter;

/**
 * Find acceptable filters
 * @param Search $search
 * @param array $filters
 * @return array
 */
function findFilters(Search $search, array $filters): array
{
    $data = $search->findAcceptableFiltersCount($filters);
    foreach ($data as &$filterValues) {
        ksort($filterValues);
    }
    unset($filterValues);
    return ['data' => $data];
}

/**
 * Find products using filters
 * @param Search $search
 * @param array $filters
 * @param string $index
 * @param int $pageLimit
 * @return array
 */
function findProducts(Search $search, array $filters, string $index, int $pageLimit): array
{
    // find product id
    $data = $search->find($filters);
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
$searchIndex = new Index();
$searchIndex->setData($indexData);
// create search instance
$search = new Search($searchIndex);
$result = [
    'filters' => findFilters($search, $filters),
    'results' => findProducts($search, $filters, $index, $pageLimit),
];
echo json_encode($result);