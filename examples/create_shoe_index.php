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
$data =  include './data/shoe-db.php';

foreach ($data as $item) {
    $recordId = $item['id'];
    $itemData = [
        'category' => $item['category'],
        'brand' => $item['brand']
    ];
    $itemData = array_merge($itemData, $item['features']);
    $storage->addRecord($recordId, $itemData);
}
$storage->optimize();
// save index data to some storage
$indexData = $storage->export();
// We will use file for example
file_put_contents('./data/shoe-index.json', json_encode($indexData));
echo 'Index created' . PHP_EOL;
