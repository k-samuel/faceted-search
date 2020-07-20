<?php

include './autoload.php';

use KSamuel\FacetedSearch\Index;
use KSamuel\FacetedSearch\Search;
use KSamuel\FacetedSearch\Filter\ValueFilter;

// load index by product category (use request params)
$indexData = json_decode(file_get_contents('./data/oils-index.json'), true);
$searchIndex = new Index();
$searchIndex->setData($indexData);
// create search instance
$search = new Search($searchIndex);

// do not use $_POST in production code
$request = $_POST['request'] ?? null;
$filters = [];
if (isset($_POST['filters'])) {
    $filtersData = json_decode($_POST['filters'], true);
    foreach ($filtersData as $name => $values) {
        $filters[] = new ValueFilter($name, $values);
    }
}

$pageLimit = 40;
if ($request == 'filters') {
    $data = $search->findAcceptableFilters($filters);
    foreach ($data as $filterName => &$filterValues) {
        sort($filterValues);
    }
    unset($filterValues);
    $result = [
        'data' => $data
    ];
} elseif ($request == 'data') {
    $data = $search->find($filters);
    $resultItems = [];
    $count = count($data);
    if (!empty($data)) {
        $data = array_chunk($data, $pageLimit);
        $data = $data[0];
        $db = include './data/oils-db.php';
        foreach ($data as $id) {
            $resultItems[] = $db[$id];
        }
    }
    $result = ['data' => $resultItems, 'count' => $count];
} else {
    $result = ['data' => []];
}
echo json_encode($result);