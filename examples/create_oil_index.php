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
$data =  include './data/oils-db.php';

foreach ($data as $item) {
    $recordId = $item['id'];
    $itemData = [
        'maker' => $item['fields']['maker'],
        'viscosity' => $item['fields']['viscosity'],
        'volume' => $item['fields']['volume'],
    ];
    $searchIndex->addRecord($recordId, $itemData);
}
$searchIndex->optimize();
// save index data to some storage
$indexData = $searchIndex->getData();
// We will use file for example
file_put_contents('./data/oils-index.json', json_encode($indexData));
echo 'Index created' . PHP_EOL;
