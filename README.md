[![PHP Version](https://img.shields.io/badge/php-7.4%2B-blue.svg)](https://packagist.org/packages/k-samuel/faceted-search)
[![Total Downloads](https://img.shields.io/packagist/dt/k-samuel/faceted-search.svg?style=flat-square)](https://packagist.org/packages/k-samuel/faceted-search)
![Build and Test](https://github.com/k-samuel/faceted-search/workflows/Build%20and%20Test/badge.svg?branch=master&event=push)
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/b9d174969c1b457fa8a6c3b753266698)](https://www.codacy.com/gh/k-samuel/faceted-search/dashboard?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=k-samuel/faceted-search&amp;utm_campaign=Badge_Grade)
[![Codacy Badge](https://app.codacy.com/project/badge/Coverage/b9d174969c1b457fa8a6c3b753266698)](https://www.codacy.com/gh/k-samuel/faceted-search/dashboard?utm_source=github.com&utm_medium=referral&utm_content=k-samuel/faceted-search&utm_campaign=Badge_Coverage)
# PHP Faceted search library 3.x

Simplified and fast faceted search without using any additional servers such as ElasticSearch, etc.

It can easily process up to 500,000 items with 10 properties. Create individual indices for product groups or categories and you won't need to scale or use more complex tools for a long time. The software is more effective when operates together with Roadrunner, Swoole, etc.

In addition to faceted filters, it supports exclusive filters. The software is optimized for uncompromising performance.

[Changelog](./changelog.md) | [2.x version](https://github.com/k-samuel/faceted-search/tree/2.x)

## Install

`
composer require k-samuel/faceted-search
`

## Aggregates
The main advantage of the library is the fast and simplified building of aggregates. 

Aggregates in Simple Terms

<img align="left" width="100" vspace="4" hspace="4" src="https://github.com/k-samuel/faceted-search/blob/master/docs/filters.png">
Imagine that a user has chosen several filters in the interface. We need to update the interface so that only filters compliant with the user’s choice (overlapping product properties) are represented in a list of available filters. We also have to display a number of available products hidden behind each filter.

Every time a user selects a new parameter, аt the code level, determine how many options are available based on the user's selection and display a new list of filters in the interface

This is simple enough, even if products have different structure of properties.
```php
<?php
  $query = (new AggregationQuery())->filters($filters);
  $filterData = $search->aggregate($query);
```


## Notes

We recommend to create an individual index for each product category or type and include in such index only fields of concern.

Use your database to store frequently changing fields, such as price, quantity, etc. The faceted search should be used for preliminary data filtering.

Try to reduce the number of records processed. Try to index only products that, for example, are held in stock to exclude processing data on unavailable products.

## Performance tests

Testing on a set of products with ten attributes, searching with filters on three fields.

v3.1.0 Bench PHP 8.2.10 + JIT + opcache (no xdebug extension)

 ArrayIndex

|  Items count | Memory | Query      | Aggregate  | Aggregate & Count | Sort by field | Results Found |
| -----------: | -----: | ---------: | ---------: | ----------------: | ------------: | ------------: |
|       10,000 |   ~3Mb | ~0.0003 s. | ~0.0006 s. |         ~0.001 s. |    ~0.0002 s. |           907 |
|       50,000 |  ~20Mb |  ~0.001 s. |  ~0.002 s. |         ~0.006 s. |    ~0.0005 s. |          4550 |
|      100,000 |  ~40Mb |  ~0.002 s. |  ~0.006 s. |         ~0.013 s. |     ~0.001 s. |          8817 |
|      300,000 |  ~95Mb |  ~0.006 s. |  ~0.016 s. |         ~0.036 s  |     ~0.002 s. |         26891 |
|    1,000,000 | ~329Mb |  ~0.024 s. |  ~0.053 s. |         ~0.134 s. |     ~0.009 s. |         90520 |
| 1,000,000 UB | ~324Mb |  ~0.046 s. |  ~0.078 s. |         ~0.159 s. |     ~0.015 s. |        179856 |

FixedArrayIndex

|  Items count | Memory |  Query     | Aggregate  | Aggregate & Count | Sort by field | Results Found |
| -----------: | -----: | ---------: | ---------: | ----------------: | ------------: | ------------: |
|       10,000 |   ~2Mb | ~0.0005 s. | ~0.0009 s. |         ~0.002 s. |    ~0.0003 s. |           907 |
|       50,000 |  ~12Mb |  ~0.002 s. |  ~0.003 s. |         ~0.012 s. |    ~0.0007 s. |          4550 |
|      100,000 |  ~23Mb |  ~0.006 s. |  ~0.010 s. |         ~0.029 s. |     ~0.001 s. |          8817 |
|      300,000 |  ~70Mb |  ~0.012 s. |  ~0.022 s. |         ~0.072 s. |     ~0.003 s. |         26891 |
|    1,000,000 | ~233Mb |  ~0.045 s. |  ~0.070 s. |         ~0.257 s. |     ~0.012 s. |         90520 |
| 1,000,000 UB | ~233Mb |  ~0.068 s. |  ~0.101 s. |         ~0.290 s. |     ~0.017 s. |        179856 |

 *(Apple M2 macOS 14.0)*

* Items count - Number of products included in an index
* Memory - RAM used by an index
* Query - Time taken to generate a filtered product list
* Aggregate - Generation of a list of available filter values for products found. A list of common properties and their values for products found (Aggregates)
* Aggregate & Count - Generation of a list of available filter values for products found. A list of common properties and their values for products found and counting of products corresponding to each filter (Aggregates)
* Sort by field - Time taken to sort results by one of the fields.
* Results Found - The number of products founds
* UB - Unbalanced dataset (uneven distribution of values in fields)


Benchmark of a library experimental port at Golang https://github.com/k-samuel/go-faceted-search

Bench v0.3.3 go 1.21.1 darwin/arm64 with parallel aggregates. 

| Items count     | Memory   | Query            | Aggregate & Count        | Sort by field| Results Found    |
|----------------:|---------:|-----------------:|-------------------------:|-------------:|-----------------:|
| 10,000          | ~7Mb     | ~0.0002 s.       | ~0.002 s.                | ~0.0001 s.   | 907              |
| 50,000          | ~14Mb    | ~0.001 s.        | ~0.017 s.                | ~0.001 s.    | 4550             |
| 100,000         | ~21Mb    | ~0.002 s.        | ~0.037 s.                | ~0.001 s.    | 8817             |
| 300,000         | ~47Mb    | ~0.008 s.        | ~0.107 s.                | ~0.004 s.    | 26891            |
| 1,000,000       | ~140Mb   | ~0.031 s.        | ~0.363 s.                | ~0.015 s.    | 90520            |
| 1,000,000 UB    | ~138Mb   | ~0.059 s.        | ~0.899 s.                | ~0.028 s.    | 179856           |
 
 *(Apple M2 macOS 14.0)*

*The internal structure of index arrangement in versions on PHP and Golang will be different starting from experimental port ver. 0.0.3 due to peculiarities of the Hash Map internal structure in these languages. In Go, we had to stop using Hash Map to make data storage in slices more effective, which initially allowed us to match PHP version performance.*

*In PHP, array (hashMap) is more effective for the current task due to using DoubleLinkedList and HashMap key packing.*

*Go has more effective methods of reduction of the size of slices without copying data (used for list deduplication). This allows to find overlapping using sorted slices.*

*Further comparison makes little sense because of different algorithms.*

## Examples

Create an index using console/crontab etc.
```php
<?php
use KSamuel\FacetedSearch\Index\Factory;

// Create search index with ArrayStorage using Factory method
$search = (new Factory)->create(Factory::ARRAY_STORAGE);
$storage = $search->getStorage();
/*
 * Get product data from data base
 */
$data = [
    ['id'=>7, 'color'=>'black', 'price'=>100, 'sale'=>true, 'size'=>36],   
    ['id'=>9, 'color'=>'green', 'price'=>100, 'sale'=>true, 'size'=>40], 
    // ....
];

foreach($data as $item){ 
   $recordId = $item['id'];
   // no need to create faceted index by id (there are no filters by it)
   unset($item['id']);
   $storage->addRecord($recordId, $item);
}

// You can run index optimization before using it (since v2.2.0).
// The procedure may be run once after changing data
// Optimization may take several seconds; you shouldn’t run optimization when the user query is in process.
$storage->optimize();

// saving index data in your warehouse for further reuse 
$indexData = $storage->export();
// To simplify the example we used json file. You need to use data base or cache
file_put_contents('./first-index.json', json_encode($indexData));
```

Using in application

```php
<?php
use KSamuel\FacetedSearch\Index\Factory;
use KSamuel\FacetedSearch\Search;
use KSamuel\FacetedSearch\Filter\ValueFilter;
use KSamuel\FacetedSearch\Filter\ExcludeValueFilter;
use KSamuel\FacetedSearch\Filter\ValueIntersectionFilter;
use KSamuel\FacetedSearch\Filter\RangeFilter;
use KSamuel\FacetedSearch\Query\SearchQuery;
use KSamuel\FacetedSearch\Query\AggregationQuery;
use KSamuel\FacetedSearch\Query\Order;

// load index of the required product category (defined by query parameters)
$indexData = json_decode(file_get_contents('./first-index.json'), true);
$search = (new Factory)->create(Factory::ARRAY_STORAGE);
$search->setData($indexData);

// get parameters of request and create search filters
$filters = [
    // Values to search 
    new ValueFilter('color', ['black','green']), // ANY OF  (OR condition)
    // RangeFilter example for numeric property ranges (min - max)
    new RangeFilter('size', ['min'=>36, 'max'=>40]),
    // You can exclude records with specific values using ExcludeValueFilter / ExcludeRangeFilter
    new ExcludeValueFilter('type', ['used']),

    // You can select items with required multiple values of each record
    // Can be used for items with multiple field values:
    // ['id'=>2, 'brand'=>'Pony', 'purpose'=>['hunting', 'fishing', 'sports']]
    new ValueIntersectionFilter('purpose', ['hunting','fishing']) // AND condition
];
// create SearchQuery
$query = (new SearchQuery())->filters($filters);
// Find records using filters. Note, it doesn't guarantee sorted result
$records = $search->query($query);
// Now we have filtered list of record identifiers.
// Find records in your storage and send results into client application.

// Also we can send acceptable filters values for current selection.
// It can be used for updating client UI.
$query = (new AggregationQuery())->filters($filters);
$filterData = $search->aggregate($query);

// If you want to get acceptable filters values with items count use $search->aggregate
// note that filters is not applied for itself for counting
// values count of a particular field depends only on filters imposed on other fields.
// Sort results using $query->sort(direction,flags)
$query = (new AggregationQuery())->filters($filters)->countItems()->sort();
$filterData = $search->aggregate($query);


// If $filters is an empty array [] or not passed into AggregationQuery, all acceptable values will be returned
$query = (new AggregationQuery());
$filterData = $search->aggregate($query);

// You can sort search query results by field using FacetedIndex
$query = (new SearchQuery())->filters($filters)->sort('price', Order::SORT_DESC);
$records = $search->query($query);


```

### Indexers

If there are too many values for a certain field in your data, you may use Range Indexer to accelerate RangeFilter operation. For example, searching by price ranges of products. Prices can be divided into intervals with a required increment.

Please, remember that RangeFilter is a rather slow solution, and it’s better to avoid facets with high value variability.

```php
<?php
use KSamuel\FacetedSearch\Index\Factory;
use KSamuel\FacetedSearch\Search;
use KSamuel\FacetedSearch\Indexer\Number\RangeIndexer;
use KSamuel\FacetedSearch\Filter\RangeFilter;

$search = (new Factory)->create(Factory::ARRAY_STORAGE);
$storage = $search->getStorage();
$rangeIndexer = new RangeIndexer(100);
$storage->addIndexer('price', $rangeIndexer);

$storage->addRecord(1,['price'=>90]);
$storage->addRecord(2,['price'=>100]);
$storage->addRecord(3,['price'=>150]);
$storage->addRecord(4,['price'=>200]);

$filters = [
  new RangeFilter('price', ['min'=>100,'max'=>200])
];

$query = (new SearchQuery())->filters($filters);
$search->query($query);

// will return [2,3,4]
```

Sorting values inside the range is only possible during the process of index creation, since this aspect is lost in case with real value. Thus, when using RangeIndexer you shouldn’t add individual values to a ready index. As a way to solve this problem, library adds new values to the end of the range and sorts them only between themselves (sorts new values and adds them to the end).

This specific feature makes sense only when you use results sorting by the field that is a range indexed using RangeIndexer.

RangeListIndexer allows creating your own ranges without using an increment as in case with RangeIndexer

```php
<?php
use KSamuel\FacetedSearch\Index\Factory;
use KSamuel\FacetedSearch\Indexer\Number\RangeListIndexer;

$search = (new Factory)->create(Factory::ARRAY_STORAGE);
$storage = $search->getStorage();
$rangeIndexer = new RangeListIndexer([100,500,1000]); // (0-99)[0],(100-499)[100],(500-999)[500],(1000 & >)[1000] 
$storage->addIndexer('price', $rangeIndexer);
```
Also, you can create your own indexers with range detection method


### FixedArrayIndex

FixedArrayIndex is slower than ArrayIndex, but it uses much less RAM. FixedArrayIndex data are compatible with ArrayIndex.

```php
<?php
use KSamuel\FacetedSearch\Index\Factory;

$search = (new Factory)->create(Factory::FIXED_ARRAY_STORAGE);
$storage = $search->getStorage();
/*
 * Get product data from data base
 * Sort data by $recordId before using Index->addRecord it can improve performance 
 */
$data = [
    ['id'=>7, 'color'=>'black', 'price'=>100, 'sale'=>true, 'size'=>36],   
    ['id'=>9, 'color'=>'green', 'price'=>100, 'sale'=>true, 'size'=>40], 
    // ....
];
foreach($data as $item){ 
   $recordId = $item['id'];
   // no need to create faceted index by id (there are no filters by it)
   unset($item['id']);
   $storage->addRecord($recordId, $item);
}

// You can run index optimization before using it (since v2.2.0).
// The procedure may be run once after changing data
// Optimization may take several seconds; you shouldn’t run optimization when the user query is in process.
$storage->optimize();
// saving index data in your warehouse for further reuse
$indexData = $storage->export();
// To simplify the example we used json file. You need to use data base or cache
file_put_contents('./first-index.json', json_encode($indexData));

// ArrayStorage and FixedArrayStorage indices data are completely compatible. You can create both indices using saved data. 
$arrayIndex = (new Factory)->create(Factory::ARRAY_STORAGE);
$arrayIndex->setData($indexData);
```

### Filter. Self-Filtration Features

When building aggregates, self-filtering of properties is disabled. This allows the user selecting a different value of the same field for filtering (switch the selection) with filter by a certain value of such field being on.

Example. The user wants to find a phone with 32Gb RAM, ticks this checkbox from the provided list (16, 32, 64). If self-filtering is on, then other options will disappear from the user interface. Only 32 Gb value will remain as it will be filtered on the basis of the user’s choice. In this case the user won’t be able to change his/her choice to 64 Gb or 16 Gb.

When building aggregates, field values are used to limit the list of available options of other fields. For example: Filter by “size” field value is used to limit the list of “brand” field results.

Everything depends on your library use scenario. Library was initially designed to simplify the user UI building. If you use library for technical analysis or statistics, enabling self-filtering will help you get expected results.

For all filters:
```php
$query = (new AggregationQuery())->filters($filters)->countItems()->sort()->selfFiltering(true);
```

For individual filter:
```php
$filters[] = (new ValueIntersectionFilter('size', [12,32]))->selfFiltering(true);
```


### More Examples
* [Demo](./examples)
* [Performance test](./tests/performance/readme.md)
* [Bench](./tests/benchmark/readme.md)

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

# Q&A
* [Is it possible somehow to implement a full-text filter?](https://github.com/k-samuel/faceted-search/issues/3)
* [Would that be possible to use a DB as an index instead of a json file?](https://github.com/k-samuel/faceted-search/issues/5)
* [Article about project history and base concepts (in Russian)](https://habr.com/ru/post/595765/)