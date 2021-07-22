<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include '../vendor/autoload.php';

use KSamuel\FacetedSearch\Index;
use KSamuel\FacetedSearch\Search;
use KSamuel\FacetedSearch\Filter\ValueFilter;

$index = 'oils';
$pageLimit = 20;
if($_GET['cat'] === 'shoe'){
    $index = 'shoe';
}
$filters = [];
if (isset($_POST['filters'])) {
    $filtersData = json_decode($_POST['filters'], true);
    foreach ($filtersData as $name => $values) {
        $filters[] = new ValueFilter($name, $values);
    }
}



// load index by product category (use request params)
$indexData = json_decode(file_get_contents('./data/'.$index.'-index.json'), true);
$searchIndex = new Index();
$searchIndex->setData($indexData);
// create search instance
$search = new Search($searchIndex);

function findFilters(Search $search, array $filters):array
{
    $data = $search->findAcceptableFiltersCount($filters);
    foreach ($data as &$filterValues) {
        ksort($filterValues);
    }
    unset($filterValues);
    return ['data' => $data];
}

function findProducts(Search $search, array $filters, string $index, int $pageLimit):array{
    // find product id
    $data = $search->find($filters);
    $resultItems = [];
    $count = count($data);
    if (!empty($data)) {
        $data = array_chunk($data, $pageLimit);
        $data = $data[0];
        // get product info from DB
        $db = include './data/'.$index.'-db.php';
        foreach ($data as $id) {
            $resultItems[] = $db[$id];
        }
    }
    return ['data' => $resultItems, 'count' => $count, 'limit' => $pageLimit];
}

$result = [
    'filters' => findFilters($search, $filters),
    'results' => findProducts($search, $filters, $index, $pageLimit),
];
echo json_encode($result);