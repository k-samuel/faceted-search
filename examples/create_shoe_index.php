<?php
/*
 * Create faceted index from data base
 */

use KSamuel\FacetedSearch\Index\ArrayIndex;

include '../vendor/autoload.php';

$searchIndex = new ArrayIndex();
/*
 * Getting products data from DB
 */
$data =  include './data/shoe-db.php';

foreach ($data as $item) {
    $recordId = $item['id'];
    $itemData = [
        'category' => $item['category'],
        'brand' => $item['brand']
    ];
    $itemData = array_merge($itemData, $item['features']);
    $searchIndex->addRecord($recordId, $itemData);
}
// save index data to some storage
$indexData = $searchIndex->getData();
// We will use file for example
file_put_contents('./data/shoe-index.json', json_encode($indexData));
