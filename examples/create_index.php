<?php
/*
 * Create faceted index from data base
 */
include './autoload.php';

$searchIndex = new \KSamuel\FacetedSearch\Index();
/*
 * Getting products data from DB
 */
$data =  include './data/oils-db.php';

foreach($data as $item){
    $recordId = $item['id'];
    $itemData = [
        'maker' => $item['fields']['maker'],
        'viscosity' => $item['fields']['viscosity'],
        'volume' => $item['fields']['volume'],
    ];
    $searchIndex->addRecord($recordId, $itemData);
}
// save index data to some storage
$indexData = $searchIndex->getData();
// We will use file for example
file_put_contents('./data/oils-index.json', json_encode($indexData));

