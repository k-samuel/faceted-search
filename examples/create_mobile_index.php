<?php
/*
 * Create faceted index from data base
 */

use KSamuel\FacetedSearch\Index\Factory;
use KSamuel\FacetedSearch\Indexer\Number\RangeIndexer;

include '../vendor/autoload.php';

$searchIndex = (new Factory)->create(Factory::ARRAY_STORAGE);
$storage = $searchIndex->getStorage();
/*
 * Getting products data from DB
 */
$data =  include './data/mobile-db.php';

$rangeIndexer = new RangeIndexer(200);
$storage->addIndexer('price_range', $rangeIndexer);

foreach ($data as $item) {
    $recordId = $item['id'];
    unset($item['id']);
    unset($item['model']);
    $item['price_range'] = $item['price'];
    $itemData = $item;
    $storage->addRecord($recordId, $itemData);
}
$storage->optimize();
// save index data to some storage
$indexData = $storage->export();
// We will use file for example
file_put_contents('./data/mobile-index.json', json_encode($indexData));
echo 'Index created' . PHP_EOL;
