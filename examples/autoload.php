<?php
$files = [
     '../src/Index.php',
     '../src/Search.php',
    '../src/Filter/FilterInterface.php',
    '../src/Filter/AbstractFilter.php',
    '../src/Filter/RangeFilter.php',
    '../src/Filter/ValueFilter.php',
    '../src/Indexer/IndexerInterface.php',
    '../src/Indexer/Number/RangeListIndexer.php',
    '../src/Indexer/Number/RangeIndexer.php',
 ];

foreach ($files as $file){
    include  $file;
}