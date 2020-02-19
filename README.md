[![PHP Version](https://img.shields.io/badge/php-7.3%2B-blue.svg)](https://packagist.org/packages/k-samuel/faceted-search)
[![Total Downloads](https://img.shields.io/packagist/dt/k-samuel/faceted-search.svg?style=flat-square)](https://packagist.org/packages/k-samuel/faceted-search)
[![Build Status](https://travis-ci.org/k-samuel/faceted-search.svg?branch=master)](https://travis-ci.org/k-samuel/faceted-search)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/b9d174969c1b457fa8a6c3b753266698)](https://www.codacy.com/manual/kirill.a.egorov/faceted-search?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=k-samuel/faceted-search&amp;utm_campaign=Badge_Grade)
[![Codacy Badge](https://api.codacy.com/project/badge/Coverage/b9d174969c1b457fa8a6c3b753266698)](https://www.codacy.com/manual/kirill.a.egorov/faceted-search?utm_source=github.com&utm_medium=referral&utm_content=k-samuel/faceted-search&utm_campaign=Badge_Coverage)
# PHP Faceted search library

Simple and fast faceted search without external servers like ElasticSearch and others.

Easily handles 100,000 products with 10 properties. If you divide the indexes into product groups or categories, 
then for a long time you will not need scaling and more serious tools.

## Install

`
composer require k-samuel/faceted-search
`

## Example 

Best Practice is to get separate index for each goods category or product type and index only required fields

If price and quantity of your products frequently changes, it is better to keep this data in DB and use facets 
for pre filtering.  In that case you can decrease number of checked records by setting the search list into the 
second argument of $search->find method. For example list of ProductId in stock to exclude not available products.

Create faceted index using console and Crontab
```php
<?php
use KSamuel\FacetedSearch\Index;

$searchIndex = new Index();
/*
 * Getting products data from DB
 */
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

Using in your application

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
$filters = [
    new ValueFilter('color', ['black']),
    // RangeFilter example for numeric property ranges (min - max)
    new RangeFilter('size', ['min'=>36, 'max'=>40])
];
$records = $search->find($filters);
// Now we have filtered list of record identifiers.
// Find records in your storage and send results into client application

// Also we can send acceptable filters values for current selection.
// It can be used for updating client ui.
$filterData = $search->findAcceptableFilters($filters);

// If $filters is an empty array [], all acceptable values will be returned without filtering
$filterData = $search->findAcceptableFilters([]);
```