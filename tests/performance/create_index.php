<?php

require '../../vendor/autoload.php';

$dataDir = './';
$t = microtime(true);
$indexData = json_decode(file_get_contents($dataDir . 'data.json'), true);

$index = new \KSamuel\FacetedSearch\Index();
$rangeIndexer = new \KSamuel\FacetedSearch\Indexer\Number\RangeIndexer(250);
$index->addIndexer('price', $rangeIndexer);

foreach ($indexData as $id => $rec) {
    $index->addRecord($id, $rec);
}

file_put_contents($dataDir . 'facet.json', json_encode($index->getData()));

echo 'total time: ' . number_format(microtime(true) - $t, 3) . PHP_EOL;