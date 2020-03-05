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

## Examples 

Its better to create index for each goods category or product type and index only required fields.

If price and quantity of your products frequently changes, it is better to keep them in database and use facets 
for pre filtering. You can decrease number of checked records by setting records list to search in. For example list of 
ProductId in stock to exclude not available products.

Create faceted index using console/crontab etc.
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
// save index data to some storage 
$indexData = $searchIndex->getData();
// We will use file for example
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
// create search instance
$search = new Search($searchIndex);
// get request params and create search filters
$filters = [
    new ValueFilter('color', ['black']),
    // RangeFilter example for numeric property ranges (min - max)
    new RangeFilter('size', ['min'=>36, 'max'=>40])
];
// find records using filters
$records = $search->find($filters);
// Now we have filtered list of record identifiers.
// Find records in your storage and send results into client application.

// Also we can send acceptable filters values for current selection.
// It can be used for updating client UI.
$filterData = $search->findAcceptableFilters($filters);

// If $filters is an empty array [], all acceptable values will be returned
$filterData = $search->findAcceptableFilters([]);
```

### Indexers

To speed up the search of RangeFilter by data with high variability of values, you can use the Range Indexer.

For example, a search on product price ranges. Prices can be divided into ranges with the desired step.

```php
<?php
use KSamuel\FacetedSearch\Index;
use KSamuel\FacetedSearch\Search;
use KSamuel\FacetedSearch\Indexer\Number\RangeIndexer;
use KSamuel\FacetedSearch\Filter\RangeFilter;

$index = new Index();
$rangeIndexer = new RangeIndexer(100);
$index->addIndexer('price', $rangeIndexer);

$index->addRecord(1,['price'=>90]);
$index->addRecord(2,['price'=>100]);
$index->addRecord(3,['price'=>150]);
$index->addRecord(4,['price'=>200]);


$filters = [
  new RangeFilter('price', ['min'=>100,'max'=>200])
];

$search = new Search($index);
$search->find($filters);

// will return [2,3]
```
RangeListIndexer allows you to use custom ranges list
```php
<?php
use KSamuel\FacetedSearch\Index;
use KSamuel\FacetedSearch\Indexer\Number\RangeListIndexer;

$index = new Index();
$rangeIndexer = new RangeListIndexer([100,500,1000]); // (0-99)[0],(100-499)[100],(500-999)[500],(1000 & >)[1000] 
$index->addIndexer('price', $rangeIndexer);
```
Also you can create your own indexers with range detection method


### More Examples 
* [Performance test. Create index for 100.000 goods](./tests/performance/create_index.php)
* [Performance test. Find records in large index](./tests/performance/find.php)