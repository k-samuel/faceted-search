[![PHP Version](https://img.shields.io/badge/php-7.3%2B-blue.svg)](https://packagist.org/packages/k-samuel/faceted-search)
[![Total Downloads](https://img.shields.io/packagist/dt/k-samuel/faceted-search.svg?style=flat-square)](https://packagist.org/packages/k-samuel/faceted-search)
![Build and Test](https://github.com/k-samuel/faceted-search/workflows/Build%20and%20Test/badge.svg?branch=master&event=push)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/b9d174969c1b457fa8a6c3b753266698)](https://www.codacy.com/manual/kirill.a.egorov/faceted-search?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=k-samuel/faceted-search&amp;utm_campaign=Badge_Grade)
[![Codacy Badge](https://api.codacy.com/project/badge/Coverage/b9d174969c1b457fa8a6c3b753266698)](https://www.codacy.com/manual/kirill.a.egorov/faceted-search?utm_source=github.com&utm_medium=referral&utm_content=k-samuel/faceted-search&utm_campaign=Badge_Coverage)
# PHP Faceted search library

Simple and fast faceted search without external servers like ElasticSearch and others.

Easily handles 300,000 products with 10 properties. If you divide the indexes into product groups or categories, 
then for a long time you will not need scaling and more serious tools. Especially in conjunction with 
RoadRunner or Swoole.

The library is optimized for performance at the expense of RAM consumption.

[Changelog](./changelog.md)

## Install

`
composer require k-samuel/faceted-search
`

## Performance tests

Tests on sets of products with 10 attributes, search with filters by 3 fields.

PHP 7.4.21 (no xdebug extension)

| Items count     | Memory   | Find             | Get Filters (aggregates) | Sort by field| Results Found    |
|----------------:|---------:|-----------------:|-------------------------:|-------------:|-----------------:|
| 10,000          | ~7Mb     | ~0.0004 s.       | ~0.003 s.                | ~0.001 s.    | 866              |
| 50,000          | ~49Mb    | ~0.004 s.        | ~0.02 s.                 | ~0.007 s.    | 4538             |
| 100,000         | ~98Mb    | ~0.008 s.        | ~0.05 s.                 | ~0.01 s.     | 8993             |
| 300,000         | ~236Mb   | ~0.034 s.        | ~0.18 s.                 | ~0.11 s.     | 27281            |
| 1000,000        | ~820Mb   | ~0.126 s.        | ~0.76 s.                 | ~0.44 s.     | 89403            |

PHP 8.0.12 + JIT (no xdebug extension)

| Items count     | Memory   | Find             | Get Filters (aggregates) | Sort by field| Results Found    |
|----------------:|---------:|-----------------:|-------------------------:|-------------:|-----------------:|
| 10,000          | ~7Mb     | ~0.0004 s.       | ~0.003 s.                | ~0.001 s.    | 866              |
| 50,000          | ~49Mb    | ~0.007 s.        | ~0.03 s.                 | ~0.008 s.    | 4538             |
| 100,000         | ~98Mb    | ~0.016 s.        | ~0.07 s.                 | ~0.02 s.     | 8993             |
| 300,000         | ~236Mb   | ~0.047 s.        | ~0.23 s.                 | ~0.15 s.     | 27281            |
| 1000,000        | ~820Mb   | ~0.164 s.        | ~0.86 s.                 | ~0.46 s.     | 89403            |


* Items count - Products in index
* Memory - RAM used for index
* Find - time of getting list of products filtered by 3 fields
* Get Filters - find acceptable filter values for found products. 
List of common properties and their values for found products (Aggregates)
* Sort by field - time of sorting found results by field value
* Results Found - count of found products (Find)

## Notes 

_* Create index for each product category or type and index only required fields._


Use database to keep frequently changing fields (price/quantity/etc) and facets for pre-filtering.

You can decrease the number of processed records by setting records list to search in. 
For example: list of ProductId "in stock" to exclude not available products.

## Examples

Create index using console/crontab etc.
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
   $searchIndex->addRecord($recordId, $item);
}
// save index data to some storage 
$indexData = $searchIndex->getData();
// We will use file for example
file_put_contents('./first-index.json', json_encode($indexData));
```

Using in application

```php
<?php
use KSamuel\FacetedSearch\Index;
use KSamuel\FacetedSearch\Search;
use KSamuel\FacetedSearch\Filter\ValueFilter;
use KSamuel\FacetedSearch\Filter\RangeFilter;
use KSamuel\FacetedSearch\Sorter\ByField;

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

// If you want to get acceptable filters values with items count use findAcceptableFiltersCount
// note that filters is not applied for itself for counting
// values count of a particular field depends only on filters imposed on other fields 
$filterData = $search->findAcceptableFiltersCount($filters);


// If $filters is an empty array [], all acceptable values will be returned
$filterData = $search->findAcceptableFilters([]);

// Also you can sort results using FacetedIndex
$sorter = new ByField($searchIndex);
$records = $sorter->sort($records, 'price', ByField::SORT_DESC);




```

### Indexers

To speed up the search of RangeFilter by data with high variability of values, you can use the Range Indexer.
For example, a search on product price ranges. Prices can be divided into ranges with the desired step.

Note that RangeFilter is slow solution, it is better to avoid facets for highly variadic data

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
Also, you can create your own indexers with range detection method

### More Examples 
* [Demo](./examples)  
* [Performance test](./tests/performance/readme.md)


### Tested but discarded concepts

**Bitmap**
- (+) Significantly less RAM consumption
- (+) Comparable search speed with filtering
- (-) Limited by property variability, may not fit into the selected bit mask
- (-) Longer index creation
- (-) The need to rebuild the index after each addition of an element or at the final stage of formation
- (-) Slow aggregates creation

**Bloom Filter**
- (+) Very low memory consumption
- (-) Possibility of false positives
- (-) Slow index creation
- (-) Longer search in the list of products
- (-) Very slow aggregates

**Creating multiple indexes for different operations**
- (-) Increased consumption of RAM 