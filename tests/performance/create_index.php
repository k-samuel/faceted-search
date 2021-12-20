<?php

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
$index = new \KSamuel\FacetedSearch\Index();
$rangeIndexer = new \KSamuel\FacetedSearch\Indexer\Number\RangeIndexer(250);
$index->addIndexer('price', $rangeIndexer);

foreach ($indexData as $rec) {
    $id = $rec['id'];
    unset($rec['id']);

    $index->addRecord($id, $rec);
}

file_put_contents($dataDir . 'facet.json', json_encode($index->getData()));

echo 'total time: ' . number_format(microtime(true) - $t, 3) . PHP_EOL;