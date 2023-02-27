<?php

use KSamuel\FacetedSearch\Index\Factory;

require '../../vendor/autoload.php';

$dataDir = './';
$t = microtime(true);
$f = fopen($dataDir . 'data.json', "r");
$indexData = [];
while ($row = fgets($f)) {
    if (!empty($row)) {
        $indexData[] = json_decode($row, true);
    }
}
$index = (new Factory)->create(Factory::ARRAY_STORAGE);
$storage = $index->getStorage();
$rangeIndexer = new \KSamuel\FacetedSearch\Indexer\Number\RangeIndexer(250);
$storage->addIndexer('price', $rangeIndexer);

foreach ($indexData as $rec) {
    $id = $rec['id'];
    unset($rec['id']);

    $storage->addRecord($id, $rec);
}

file_put_contents($dataDir . 'facet.json', json_encode($storage->export()));

echo 'total time: ' . number_format(microtime(true) - $t, 3) . PHP_EOL;
