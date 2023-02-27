<?php
/*
 * Create faceted index from data base
 */

use KSamuel\FacetedSearch\Index\Factory;

include '../vendor/autoload.php';

$searchIndex = (new Factory)->create(Factory::ARRAY_STORAGE);
$storage = $searchIndex->getStorage();
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
    $storage->addRecord($recordId, $itemData);
}
$storage->optimize();
// save index data to some storage
$indexData = $storage->export();
// We will use file for example
file_put_contents('./data/oils-index.json', json_encode($indexData));
echo 'Index created' . PHP_EOL;
