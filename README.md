[![PHP Version](https://img.shields.io/badge/php-7.3%2B-blue.svg)](https://packagist.org/packages/k-samuel/faceted-search)
[![Total Downloads](https://img.shields.io/packagist/dt/k-samuel/faceted-search.svg?style=flat-square)](https://packagist.org/packages/k-samuel/faceted-search)
[![Build Status](https://travis-ci.org/dvelum/dvelum-core.svg?branch=master)](https://travis-ci.org/k-samuel/faceted-search)

# PHP Faceted search library

Simple and fast faceted search without ELK stack

## Install

`
composer require k-samuel/faceted-search
`


## Example 

Create faceted index in background
```php
<?php
use KSamuel\FacetedSearch\Index;

$searchIndex = new Index();
// Getting data from DB
// Best Practice is to get separate index for each goods category or product type  
$data = [
    ['id'=>7, 'color'=>'black', 'price'=>100, 'sale'=>true, 'size'=>36],   
    ['id'=>9, 'color'=>'green', 'price'=>100, 'sale'=>true, 'size'=>40], 
    // ....
];
foreach($data as $item){ 
   $recordId = $item['id'];
   // no ned to add faceted index by id
   unset($item['id']);
   $searchIndex->add($recordId, $item);
}
// save index data to some storage DB
$indexData = $searchIndex->getData();
// For simplifying example we will use file to store data
file_put_contents('./first-index.json', json_encode($indexData));
```

Using index in your controller

```php
<?php
use KSamuel\FacetedSearch\Index;
use KSamuel\FacetedSearch\Search;
use KSamuel\FacetedSearch\Filter\ValueFilter;
use KSamuel\FacetedSearch\Filter\RangeFilter;

// load index by product category (use request params)
$indexData = json_decode(file_get_contents('./first-index.json'), true);
$searchIndex = new Index();
$searchIndex->setData($indexData);
// create search 
$search = new Search($searchIndex);
// get request params and create search filters
$filter1 = new ValueFilter('color');
// value from request params
$filter1->setValue(['black']);

// RangeFilter example for numeric property ranges (min - max)
$filter2 = new RangeFilter('size');
$filter2->setValue(['min'=>36, 'max'=>40]);

$filters = [$filter1, $filter2];

$records = $search->find($filters);
// Now we have filtered list of record identifiers.
// Find records in your storage and send results into client application

// Also we can send acceptable filters values for current selection.// 
// It can be used for updating client ui.
// If set $filters to empty array [], all acceptable values will be returned without filtering
$filterData = $search->findAcceptableFilters($filters);
```