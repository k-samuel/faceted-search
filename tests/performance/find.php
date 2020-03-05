<?php
require '../../vendor/autoload.php';

use KSamuel\FacetedSearch\Index;
use KSamuel\FacetedSearch\Filter\ValueFilter;
use KSamuel\FacetedSearch\Filter\RangeFilter;
use KSamuel\FacetedSearch\Search;

$t = microtime(true);
$m = memory_get_usage();
$indexData = json_decode(file_get_contents('./facet.json'), true);
echo "index memory usage: ".(string)(int)((memory_get_usage()-$m)/1024/1024)."Mb\n";
$index = new Index();
$index->setData($indexData);

$search = new Search($index);

$filters = [
    new ValueFilter('color','black'),
    new ValueFilter('warehouse', [789,45,65,1,10]),
    new RangeFilter('price', ['min'=> 1000,'max'=>7000])
];

$results = $search->find($filters);

echo 'Results: ' . count($results) . "\n";
echo microtime(true) - $t . "s\n";




