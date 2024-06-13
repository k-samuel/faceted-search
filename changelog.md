# Changelog

### v3.2.2 (13.06.2024)

Documentation updated

### v3.2.1 (04.12.2023)

Self-filtering option for individual filter (disabled by default). [Feature Request](https://github.com/k-samuel/faceted-search/issues/37)
Advanced configuration for AggregationQuery, if enabled, then result for filter can contain only filter values.
Useful for cases with ValueIntersectionFilter  (AND condition).
```php
$filters[] = (new ValueIntersectionFilter('size', [12,32]))->selfFiltering(true);
```



### v3.2.0 (29.11.2023)

- ValueIntersectionFilter added [Feature Request](https://github.com/k-samuel/faceted-search/issues/33)
- Enable self-filtering option for AggregationQuery added


#### ValueIntersectionFilter
Default Filters example:

Find phones with memory sizes ANY OF (12, 32, 64) AND camera 12m
```php
$filters[] = new ValueFilter(‘size’, [12,32,64]);
$filters[] = new ValueFilter(‘camera’, [12]);
```

New functionality example:

Search brand "Digma" OR "Pony" where the recommended usage is for portraits AND wildlife.
Can be used for items with multiple field values 
```
<?php 
$data = [
 ['id'=>1, 'brand'=>'Digma', 'usage'=>['portraits', 'wildlife']],
 ['id'=>2, 'brand'=>'Pony', 'usage'=>['streetphoto', 'weddings','portraits']],
];

// ...

$filters[] = new ValueFilter('brand', ['Digma', 'Pony']); // ANY OF
$filters[] = new ValueIntersectionFilter('usage', ['portraits', 'wildlife']); // portraits AND wildlife
```

#### Self-filtering

Aggregates disables property self-filtering by default. It allow the user to choose another option in the interface.

Example:
User wants a phone with 32GB memory, checks the box for the desired option from (16, 32, 64). 
If self-filtering is enabled, then all other options in the UI will disappear and only 32 will remain. 
Thus, user will not be able to change his choice.

During aggregation field filter value is used to limit values only other fields. 
Example: the "size" filter condition uses to limit the list of "brand" field variations.

All depends on your use case of the library. 
Initially, the library was developed to simplify the construction of a search UI.
If you want to use the library at the level of technical analysis, statistics, etc. , then enabling self-filtering can help you to get expected results.

```php
$query = (new AggregationQuery())->filters($filters)->countItems()->sort()->selfFiltering(true);
```


#### Bench

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

### v3.1.0 (14.06.2023)

#### Exclude Filters

[Feature Request](https://github.com/k-samuel/faceted-search/issues/26)

- ExcludeValueFilter and ExcludeRageFilter added
- Updated examples demonstrates the use of most of the library's functions
- Added sorting inside RangeIndexer's sets

New filters allow the user to select values ​​and ranges to exclude from search results. 
In some cases, such functionality is convenient for users, especially when their search comes from 
understanding what they definitely do not want to see.

Usage is similar to regular filters:
```php
// ....
<?php
$query = (new AggregationQuery())
          ->filters([
                new ExcludeValueFilter('color', ['green']), // remove products with green color from results
                new ValueFilter('size', [41,42]),
          ])
          // Count items for each acceptable filter value (slower)
          ->countItems()
          // Sort results by fields and values
          ->sort();
// ...
```

Demo can be found [here](./examples). Start local server and open "Mobile Catalog" page.

#### Notes

ExcludeValueFilter slightly slows down the search (Query), while speeding up the construction of aggregates. Considering that aggregates are much slower than searches, the functionality in general has a positive effect on performance.

Found that in order to organize user-friendly behavior, additional sorting is needed within the RangeIndexer ranges. Sorting requires additional resources during index construction, and does not affect the performance of subsequent use of indexes.

Sorting within ranges is possible only during the initial creating of index, since the connection with the real value is lost. Therefore, when using the RangeIndexer, you should not use adding new single values after a complete rebuild. As a workaround new values will be added to the end of range and be sorted only inside new values. This is relevant only for cases with sorting by field indexed by RangeIndexer.

### v3.0.0 (04.03.2023)
- Removed deprecated methods.
- The code has been refactored, the complexity has been reduced.
- The library API has been slightly changed.
- Documentation and demo updated according to the new API.
- Improved performance of FixedArrayStorage.
- The new version fully supports data exported from 2.2.x indexes.


#### Api changes
```php
<?php
use KSamuel\FacetedSearch\Index\Factory;

// Index creation moved to factory method
$search = (new Factory)->create(Factory::ARRAY_STORAGE);

// The data storage is moved to a separate object
$storage = $search->getStorage();

$data = [
    ['id'=>7, 'color'=>'black', 'price'=>100, 'sale'=>true, 'size'=>36],   
    ['id'=>9, 'color'=>'green', 'price'=>100, 'sale'=>true, 'size'=>40], 
    // ....
];

foreach($data as $item){ 
   $recordId = $item['id'];
   unset($item['id']);

   // Data and indexers are now passed to the storage
   $storage->addRecord($recordId, $item);
}
$storage->optimize();

// Data export is now performed by a separate method
$indexData = $storage->export();

file_put_contents('./first-index.json', json_encode($indexData));
```

v3.0.0 Bench ArrayIndex  PHP 8.2.3 + JIT + opcache (no xdebug extension)

|  Items count | Memory |       Find | Get Filters (aggregate) | Get Filters & Count (aggregate) | Sort by field | Results Found |
| -----------: | -----: | ---------: | ----------------------: | ------------------------------: | ------------: | ------------: |
|       10,000 |   ~3Mb | ~0.0008 s. |               ~0.001 s. |                       ~0.002 s. |    ~0.0001 s. |           907 |
|       50,000 |  ~20Mb |  ~0.002 s. |               ~0.005 s. |                       ~0.010 s. |    ~0.0006 s. |          4550 |
|      100,000 |  ~40Mb |  ~0.004 s. |               ~0.012 s. |                       ~0.023 s. |    ~0.0012 s. |          8817 |
|      300,000 |  ~95Mb |  ~0.010 s. |               ~0.036 s. |                       ~0.079 s  |     ~0.004 s. |         26891 |
|    1,000,000 | ~329Mb |  ~0.039 s. |               ~0.134 s. |                       ~0.287 s. |     ~0.015 s. |         90520 |
| 1,000,000 UB | ~324Mb |  ~0.103 s. |               ~0.225 s. |                       ~0.406 s. |     ~0.032 s. |        179856 |

v3.0.0 Bench FixedArrayIndex PHP 8.2 + JIT + opcache (no xdebug extension) 

|  Items count | Memory |       Find | Get Filters (aggregate) | Get Filters & Count (aggregate) | Sort by field | Results Found |
| -----------: | -----: | ---------: | ----------------------: | ------------------------------: | ------------: | ------------: |
|       10,000 |   ~2Mb | ~0.0012 s. |               ~0.001 s. |                       ~0.005 s. |    ~0.0004 s. |           907 |
|       50,000 |  ~12Mb |  ~0.004 s. |               ~0.006 s. |                       ~0.022 s. |     ~0.001 s. |          4550 |
|      100,000 |  ~23Mb |  ~0.007 s. |               ~0.015 s. |                       ~0.048 s. |     ~0.002 s. |          8817 |
|      300,000 |  ~70Mb |  ~0.020 s. |               ~0.046 s. |                       ~0.142 s. |     ~0.005 s. |         26891 |
|    1,000,000 | ~233Mb |  ~0.081 s. |               ~0.172 s. |                       ~0.517 s. |     ~0.021 s. |         90520 |
| 1,000,000 UB | ~233Mb |  ~0.149 s. |               ~0.260 s. |                       ~0.682 s. |     ~0.039 s. |        179856 |




### v2.2.1 (26.01.2023)
Added the ability to update index data without a complete rebuild.
New methods added:
```PHP
use KSamuel\FacetedSearch\Index\ArrayIndex;

$index = new ArrayIndex();
$index->setData($dataFromStorage);

// delete record from index
$index->deleteRecord($recordId);
// replace record data with new values
$index->replaceRecord($recordId,['newField'=>'newValue'/* .... */]);

```
FixedArrayIndex also implements new methods
```PHP
use KSamuel\FacetedSearch\Index\FixedArrayIndex;

$index = new ArrayIndex();
$index->writeMode();
$index->setData($dataFromStorage);

// delete record from index
$index->deleteRecord($recordId);
// replace record data with new values
$index->replaceRecord($recordId,['newField'=>'newValue'/* .... */]);

$index->commitChanges();
```

### v2.2.0 (16.12.2022)

- New Query API
- More efficient result sorting using SearchQuery
- Ability to sort aggregation results
- Performance improvements
- Index optimization using ```$searchIndex->optimize()```
- FilterInterface changed


## New Query API
```PHP
<?php
use KSamuel\FacetedSearch\Index\ArrayIndex;
use KSamuel\FacetedSearch\Search;
use KSamuel\FacetedSearch\Query\SearchQuery;
use KSamuel\FacetedSearch\Query\AggregationQuery;
use KSamuel\FacetedSearch\Query\Order;
use KSamuel\FacetedSearch\Filter\ValueFilter;

// load index
$searchIndex = new ArrayIndex();
$searchIndex->setData($someIndexData);
// create search instance
$search = new Search($searchIndex);

// Find results
$query = (new SearchQuery())
    ->filters([
        new ValueFilter('color', ['black','white']),
        new ValueFilter('size', [41,42])
    ])
    // It is possible to set List of record id to search in. 
    // For example list of records id that found by external FullText search.
    ->inRecords([1,2,3,19,17,21/*..some input record ids..*/])
   // Now results can be sorted by field value.
   // Note! If result item has not such field then item will be excluded from results
    ->order('price', Order::SORT_DESC);

$results = $search->query(query);   

// Find Acceptable filters for user selected input
$query = (new AggregationQuery())
          ->filters([
                new ValueFilter('color', ['black','white']),
                new ValueFilter('size', [41,42])
          ])
          // Count items for each acceptable filter value (slower)
          ->countItems()
          // Sort results by fields and values
          ->sort();

$results = $search->aggregate(query);            
```
New aggregation API has changed result format for ```$search->aggregate()```
With countItems:
```PHP
 [
    'field1' => [
        'value1' => 10,
        'value2' => 20
    ]
 ]
```
Without countItems:
```PHP
 [
    'field1' => [
        'value1' => true,
        'value2' => true
    ]
 ]
```
The change was necessary to unify the results structure.
Old API produces results as before in slightly different formats for: 

```PHP
$search->findAcceptableFilters();
$search->findAcceptableFiltersCount();
```

## Backward compatibility
The version is fully backward compatible if you haven't used own filters implementations.

The old API format is available but marked as deprecated.


**FilterInterface** changed. You need to take this into account if you implemented your own versions of filters
```PHP
//Interface 
use KSamuel\FacetedSearch\Filter\FilterInterface;
//changed
public function filterResults(array $facetedData, ?array $inputIdKeys = null): array;
//replaced with
public function filterInput(array $facetedData,  array &$inputIdKeys): void;
```

## Performance

 Added index optimization method.
```php
<?php
use KSamuel\FacetedSearch\Index\ArrayIndex;

$searchIndex = new ArrayIndex();
/*
 * Getting products data from DB
 */
$data = [
    ['id'=>7, 'color'=>'black', 'price'=>100, 'sale'=>true, 'size'=>36],   
    // ....
];
foreach($data as $item){ 
   $recordId = $item['id'];
   // no need to add faceted index by id
   unset($item['id']);
   $searchIndex->addRecord($recordId, $item);
}

// You can optionally call index optimization before using (since v2.2.0). 
// The procedure can be run once after changing the index data. 
// Optimization takes a few seconds, you should not call it during the processing of user requests.
$searchIndex->optimize();

// save index data to some storage 
$indexData = $searchIndex->getData();
// We will use file for example
file_put_contents('./first-index.json', json_encode($indexData));
```
Unbalanced Dataset added to Benchmark test


v2.2.0 Bench ArrayIndex  PHP 8.2 + JIT + opcache (no xdebug extension)

|  Items count | Memory |       Find | Get Filters (aggregate) | Get Filters & Count (aggregate) | Sort by field | Results Found |
| -----------: | -----: | ---------: | ----------------------: | ------------------------------: | ------------: | ------------: |
|       10,000 |   ~3Mb | ~0.0004 s. |               ~0.001 s. |                       ~0.002 s. |    ~0.0001 s. |           907 |
|       50,000 |  ~20Mb |  ~0.001 s. |               ~0.005 s. |                       ~0.010 s. |    ~0.0004 s. |          4550 |
|      100,000 |  ~40Mb |  ~0.003 s. |               ~0.013 s. |                       ~0.023 s. |    ~0.0009 s. |          8817 |
|      300,000 |  ~95Mb |  ~0.009 s. |               ~0.034 s. |                       ~0.077 s  |     ~0.003 s. |         26891 |
|    1,000,000 | ~329Mb |  ~0.039 s. |               ~0.131 s. |                       ~0.281 s. |     ~0.014 s. |         90520 |
| 1,000,000 UB | ~324Mb |  ~0.099 s. |               ~0.218 s. |                       ~0.401 s. |     ~0.028 s. |        179856 |

v2.2.0 Bench FixedArrayIndex PHP 8.2 + JIT + opcache (no xdebug extension) 

|  Items count | Memory |       Find | Get Filters (aggregate) | Get Filters & Count (aggregate) | Sort by field | Results Found |
| -----------: | -----: | ---------: | ----------------------: | ------------------------------: | ------------: | ------------: |
|       10,000 |   ~2Mb | ~0.0007 s. |               ~0.001 s. |                       ~0.003 s. |    ~0.0002 s. |           907 |
|       50,000 |  ~12Mb |  ~0.003 s. |               ~0.007 s. |                       ~0.017 s. |    ~0.0009 s. |          4550 |
|      100,000 |  ~23Mb |  ~0.006 s. |               ~0.017 s. |                       ~0.039 s. |     ~0.001 s. |          8817 |
|      300,000 |  ~70Mb |  ~0.020 s. |               ~0.056 s. |                       ~0.120 s. |     ~0.005 s. |         26891 |
|    1,000,000 | ~233Mb |  ~0.073 s. |               ~0.207 s. |                       ~0.447 s. |     ~0.021 s. |         90520 |
| 1,000,000 UB | ~233Mb |  ~0.162 s. |               ~0.271 s. |                       ~0.609 s. |     ~0.035 s. |        179856 |


### Previous version

v2.1.5 Bench ArrayIndex  PHP 8.2 + JIT + opcache (no xdebug extension)

|  Items count | Memory |       Find | Get Filters (aggregate) | Get Filters & Count (aggregate) | Sort by field | Results Found |
| -----------: | -----: | ---------: | ----------------------: | ------------------------------: | ------------: | ------------: |
|       10,000 |   ~3Mb | ~0.0004 s. |               ~0.001 s. |                       ~0.002 s. |    ~0.0001 s. |           907 |
|       50,000 |  ~20Mb |  ~0.001 s. |               ~0.006 s. |                       ~0.011 s. |    ~0.0005 s. |          4550 |
|      100,000 |  ~40Mb |  ~0.003 s. |               ~0.014 s. |                       ~0.024 s. |     ~0.001 s. |          8817 |
|      300,000 |  ~95Mb |  ~0.010 s. |               ~0.042 s. |                        ~0.082 s |     ~0.003 s. |         26891 |
|    1,000,000 | ~329Mb |  ~0.046 s. |               ~0.164 s. |                       ~0.306 s. |     ~0.015 s. |         90520 |
| 1,000,000 UB | ~324Mb |  ~0.102 s. |               ~0.238 s. |                       ~0.446 s. |     ~0.031 s. |        179856 |

v2.1.5 Bench FixedArrayIndex PHP 8.2 + JIT + opcache (no xdebug extension) 

|  Items count | Memory |       Find | Get Filters (aggregate) | Get Filters & Count (aggregate) | Sort by field | Results Found |
| -----------: | -----: | ---------: | ----------------------: | ------------------------------: | ------------: | ------------: |
|       10,000 |   ~2Mb | ~0.0006 s. |               ~0.001 s. |                       ~0.003 s. |    ~0.0002 s. |           907 |
|       50,000 |  ~12Mb |  ~0.003 s. |               ~0.007 s. |                       ~0.017 s. |    ~0.0009 s. |          4550 |
|      100,000 |  ~23Mb |  ~0.006 s. |               ~0.017 s. |                       ~0.040 s. |     ~0.001 s. |          8817 |
|      300,000 |  ~70Mb |  ~0.019 s. |               ~0.056 s. |                       ~0.120 s. |     ~0.006 s. |         26891 |
|    1,000,000 | ~233Mb |  ~0.077 s. |               ~0.202 s. |                       ~0.455 s. |     ~0.023 s. |         90520 |
| 1,000,000 UB | ~233Mb |  ~0.146 s. |               ~0.292 s. |                       ~0.586 s. |     ~0.044 s. |        179856 |




### v2.1.6 (12.10.2022)
### Bug Fix
* [issue#8](https://github.com/k-samuel/faceted-search/issues/9) Version 2.1.5 does not allow integer names for data fields. Reported by [pixobit](https://github.com/pixobit).

# Changelog
### v2.1.5 (23.09.2022)

### Performance update
Aggregate method now up to 33 % faster.

PHPBench v2.1.5 ArrayIndex PHP 8.1.10 + JIT + opcache (no xdebug extension)

| Items count | Memory |       Find | Get Filters (aggregate) | Get Filters & Count (aggregate) | Sort by field | Results Found |
| ----------: | -----: | ---------: | ----------------------: | ------------------------------: | ------------: | ------------: |
|      10,000 |   ~6Mb | ~0.0004 s. |               ~0.001 s. |                       ~0.002 s. |    ~0.0001 s. |           907 |
|      50,000 |  ~40Mb |  ~0.001 s. |               ~0.005 s. |                       ~0.010 s. |    ~0.0005 s. |          4550 |
|     100,000 |  ~80Mb |  ~0.003 s. |               ~0.016 s. |                       ~0.029 s. |     ~0.001 s. |          8817 |
|     300,000 | ~189Mb |  ~0.011 s. |               ~0.044 s. |                        ~0.091 s |     ~0.004 s. |         26891 |
|   1,000,000 | ~657Mb |  ~0.047 s. |               ~0.169 s. |                       ~0.333 s. |     ~0.018 s. |         90520 |

PHPBench v2.1.5 FixedArrayIndex PHP 8.1.10 + JIT + opcache (no xdebug extension)

| Items count | Memory |       Find | Get Filters (aggregate) | Get Filters & Count (aggregate) | Sort by field | Results Found |
| ----------: | -----: | ---------: | ----------------------: | ------------------------------: | ------------: | ------------: |
|      10,000 |   ~2Mb | ~0.0007 s. |               ~0.001 s. |                       ~0.003 s. |    ~0.0002 s. |           907 |
|      50,000 |  ~12Mb |  ~0.003 s. |               ~0.007 s. |                       ~0.018 s. |    ~0.0009 s. |          4550 |
|     100,000 |  ~23Mb |  ~0.006 s. |               ~0.017 s. |                       ~0.040 s. |     ~0.002 s. |          8817 |
|     300,000 |  ~70Mb |  ~0.020 s. |               ~0.059 s. |                       ~0.118 s. |     ~0.006 s. |         26891 |
|   1,000,000 | ~233Mb |  ~0.079 s. |               ~0.206 s. |                       ~0.448 s. |     ~0.026 s. |         90520 |


# Changelog
### v2.1.4 (09.08.2022)

### Performance updates
* aggregate method 2x faster for cases without values count
```php
 $search->findAcceptableFilters()
```
* some optimisations of sorting method

PHPBench v2.1.4 ArrayIndex PHP 8.1.9 + JIT + opcache (no xdebug extension)

| Items count | Memory |       Find | Get Filters (aggregate) | Get Filters & Count (aggregate) | Sort by field | Results Found |
| ----------: | -----: | ---------: | ----------------------: | ------------------------------: | ------------: | ------------: |
|      10,000 |   ~6Mb | ~0.0004 s. |               ~0.001 s. |                       ~0.002 s. |    ~0.0001 s. |           907 |
|      50,000 |  ~40Mb |  ~0.001 s. |               ~0.007 s. |                       ~0.013 s. |    ~0.0005 s. |          4550 |
|     100,000 |  ~80Mb |  ~0.003 s. |               ~0.015 s. |                       ~0.028 s. |     ~0.001 s. |          8817 |
|     300,000 | ~189Mb |  ~0.012 s. |               ~0.057 s. |                        ~0.097 s |     ~0.004 s. |         26891 |
|   1,000,000 | ~657Mb |  ~0.047 s. |               ~0.233 s. |                       ~0.385 s. |     ~0.017 s. |         90520 |

PHPBench v2.1.4 FixedArrayIndex PHP 8.1.9 + JIT + opcache (no xdebug extension)

| Items count | Memory |       Find | Get Filters (aggregate) | Get Filters & Count (aggregate) | Sort by field | Results Found |
| ----------: | -----: | ---------: | ----------------------: | ------------------------------: | ------------: | ------------: |
|      10,000 |   ~2Mb | ~0.0007 s. |               ~0.002 s. |                       ~0.005 s. |    ~0.0002 s. |           907 |
|      50,000 |  ~12Mb |  ~0.003 s. |               ~0.012 s. |                       ~0.024 s. |    ~0.0009 s. |          4550 |
|     100,000 |  ~23Mb |  ~0.006 s. |               ~0.025 s. |                       ~0.047 s. |     ~0.002 s. |          8817 |
|     300,000 |  ~70Mb |  ~0.019 s. |               ~0.083 s. |                       ~0.149 s. |     ~0.006 s. |         26891 |
|   1,000,000 | ~233Mb |  ~0.077 s. |               ~0.306 s. |                       ~0.550 s. |     ~0.025 s. |         90520 |


### v2.1.3 (04.05.2022)
### Bug Fix
* [issue#8](https://github.com/k-samuel/faceted-search/issues/8) Unexpected results. Filters that do not return results are ignored for searching with multiple filters. [satanicman](https://github.com/satanicman)


### v2.1.2 (20.01.2022)
### Bug Fix
* [issue#6](https://github.com/k-samuel/faceted-search/issues/6) Broken RangeFilter from [mavmedved](https://github.com/mavmedved)



### v2.1.1 (11.01.2022)

### Performance update

* Index Refactoring. Some methods moved from Search into Index
* FixedArrayIndex performance patches. Index access is faster than "foreach" iteration

PHPBench v2.1.1 ArrayIndex PHP 8.1.0 + JIT + opcache (no xdebug extension)

| Items count | Memory |       Find | Get Filters (aggregates) | Sort by field | Results Found |
| ----------: | -----: | ---------: | -----------------------: | ------------: | ------------: |
|      10,000 |   ~6Mb | ~0.0003 s. |                ~0.002 s. |    ~0.0001 s. |           907 |
|      50,000 |  ~40Mb |  ~0.001 s. |                ~0.013 s. |    ~0.0005 s. |          4550 |
|     100,000 |  ~80Mb |  ~0.003 s. |                ~0.028 s. |     ~0.001 s. |          8817 |
|     300,000 | ~189Mb |  ~0.011 s. |                ~0.100 s. |     ~0.004 s. |         26891 |
|   1,000,000 | ~657Mb |  ~0.047 s. |                ~0.387 s. |     ~0.018 s. |         90520 |

PHPBench v2.1.1 FixedArrayIndex PHP 8.1.0 + JIT + opcache (no xdebug extension)

| Items count | Memory |       Find | Get Filters (aggregates) | Sort by field | Results Found |
| ----------: | -----: | ---------: | -----------------------: | ------------: | ------------: |
|      10,000 |   ~2Mb | ~0.0007 s. |                ~0.004 s. |    ~0.0001 s. |           907 |
|      50,000 |  ~12Mb |  ~0.003 s. |                ~0.024 s. |    ~0.0009 s. |          4550 |
|     100,000 |  ~23Mb |  ~0.006 s. |                ~0.049 s. |     ~0.001 s. |          8817 |
|     300,000 |  ~70Mb |  ~0.019 s. |                ~0.151 s. |     ~0.006 s. |         26891 |
|   1,000,000 | ~233Mb |  ~0.078 s. |                ~0.565 s. |     ~0.024 s. |         90520 |


### v2.1.0 (06.01.2022)

### Performance update and FixedArrayIndex

FixedArrayIndex is much slower than ArrayIndex but requires significant less memory.

* FixedArrayIndex added
* KSamuel\FacetedSearch\Index is deprecated use KSamuel\FacetedSearch\Index\ArrayIndex instead
* Unit and performance tests for FixedArrayIndex
* Added sorting of filter fields before processing (performance)
* Documentation updated

PHPBench v2.1.0 ArrayIndex PHP 8.1.0 + JIT + opcache (no xdebug extension)

| Items count | Memory |       Find | Get Filters (aggregates) | Sort by field | Results Found |
| ----------: | -----: | ---------: | -----------------------: | ------------: | ------------: |
|      10,000 |   ~6Mb | ~0.0003 s. |                ~0.002 s. |    ~0.0001 s. |           907 |
|      50,000 |  ~40Mb |  ~0.001 s. |                ~0.013 s. |    ~0.0005 s. |          4550 |
|     100,000 |  ~80Mb |  ~0.003 s. |                ~0.030 s. |     ~0.001 s. |          8817 |
|     300,000 | ~189Mb |  ~0.011 s. |                ~0.101 s. |     ~0.005 s. |         26891 |
|   1,000,000 | ~657Mb |  ~0.049 s. |                ~0.396 s. |     ~0.017 s. |         90520 |

PHPBench v2.1.0 FixedArrayIndex PHP 8.1.0 + JIT + opcache (no xdebug extension)

| Items count | Memory |       Find | Get Filters (aggregates) | Sort by field | Results Found |
| ----------: | -----: | ---------: | -----------------------: | ------------: | ------------: |
|      10,000 |   ~2Mb | ~0.0007 s. |                ~0.006 s. |    ~0.0002 s. |           907 |
|      50,000 |  ~12Mb |  ~0.003 s. |                ~0.027 s. |     ~0.001 s. |          4550 |
|     100,000 |  ~23Mb |  ~0.006 s. |                ~0.057 s. |     ~0.002 s. |          8817 |
|     300,000 |  ~70Mb |  ~0.021 s. |                ~0.188 s. |     ~0.007 s. |         26891 |
|   1,000,000 | ~233Mb |  ~0.080 s. |                ~0.674 s. |     ~0.032 s. |         90520 |

### v2.0.3  (30.12.2021)
Performance update

* Filter\ValueFilter optimizations
* Sorter\ByField optimizations
* Search->findRecordsMap optimizations
* PHPBench tests added

PHPBench v2.0.3 PHP 8.1.0 + JIT + opcache (no xdebug extension)

| Items count | Memory |       Find | Get Filters (aggregates) | Sort by field | Results Found |
| ----------: | -----: | ---------: | -----------------------: | ------------: | ------------: |
|      10,000 |   ~6Mb | ~0.0003 s. |                ~0.002 s. |    ~0.0001 s. |           907 |
|      50,000 |  ~40Mb |  ~0.001 s. |                ~0.013 s. |    ~0.0006 s. |          4550 |
|     100,000 |  ~80Mb |  ~0.003 s. |                ~0.029 s. |     ~0.001 s. |          8817 |
|     300,000 | ~189Mb |  ~0.011 s. |                ~0.108 s. |     ~0.005 s. |         26891 |
|   1,000,000 | ~657Mb |  ~0.052 s. |                ~0.419 s. |     ~0.018 s. |         90520 |


### v2.0.2 (13.12.2021)
* fix default example of performance tests

### v2.0.1 (16.12.2021)
* fix for PHP 8.1 deprecation of indirect floating point conversion in array keys

### v2.0.0 (13.12.2021)

Reduced Index memory usage.
Backward incompatibility, faceted index should be reindex before using new version of library.

Bench v2.0.0 PHP 8.1.0 + JIT + opcache (no xdebug extension)

| Items count | Memory |       Find | Get Filters (aggregates) | Sort by field | Results Found |
| ----------: | -----: | ---------: | -----------------------: | ------------: | ------------: |
|      10,000 |   ~6Mb | ~0.0007 s. |                ~0.004 s. |    ~0.0005 s. |           907 |
|      50,000 |  ~40Mb |  ~0.002 s. |                ~0.014 s. |     ~0.001 s. |          4550 |
|     100,000 |  ~80Mb |  ~0.004 s. |                ~0.028 s. |     ~0.001 s. |          8817 |
|     300,000 | ~189Mb |  ~0.011 s. |                ~0.104 s. |     ~0.006 s. |         26891 |
|   1,000,000 | ~657Mb |  ~0.050 s. |                ~0.426 s. |     ~0.030 s. |         90520 |

Bench v1.3.3 PHP 8.1.0 + JIT + opcache (no xdebug extension)

| Items count | Memory |       Find | Get Filters (aggregates) | Sort by field | Results Found |
| ----------: | -----: | ---------: | -----------------------: | ------------: | ------------: |
|      10,000 |   ~7Mb | ~0.0007 s. |                ~0.004 s. |    ~0.0003 s. |           907 |
|      50,000 |  ~49Mb |  ~0.002 s. |                ~0.014 s. |    ~0.0009 s. |          4550 |
|     100,000 |  ~98Mb |  ~0.004 s. |                ~0.028 s. |     ~0.002 s. |          8817 |
|     300,000 | ~242Mb |  ~0.012 s. |                ~0.112 s. |     ~0.007 s. |         26891 |
|   1,000,000 | ~812Mb |  ~0.057 s. |                ~0.443 s. |     ~0.034 s. |         90520 |


### v1.3.4 (13.12.2021)

* bugfix ```$search->findAcceptableFilters([],[1,2,3]);``` returns empty results for empty filters and none empty id list in arguments.
Thanks to [@chrisvidal](https://github.com/chrisvidal) for reporting.

### v1.3.3 (11.12.2021)
Performance update

* Added search optimization for ValueFilter. Filters are sorted before searching to reduce memory allocation

Unfortunately, the filter sequence in previous performance tests was already optimal.

Bench v1.3.3 PHP 8.1.0 + JIT + opcache (no xdebug extension)

| Items count | Memory |       Find | Get Filters (aggregates) | Sort by field | Results Found |
| ----------: | -----: | ---------: | -----------------------: | ------------: | ------------: |
|      10,000 |   ~7Mb | ~0.0007 s. |                ~0.004 s. |    ~0.0003 s. |           907 |
|      50,000 |  ~49Mb |  ~0.002 s. |                ~0.014 s. |    ~0.0009 s. |          4550 |
|     100,000 |  ~98Mb |  ~0.004 s. |                ~0.028 s. |     ~0.002 s. |          8817 |
|     300,000 | ~242Mb |  ~0.012 s. |                ~0.112 s. |     ~0.007 s. |         26891 |
|   1,000,000 | ~812Mb |  ~0.057 s. |                ~0.443 s. |     ~0.034 s. |         90520 |

Bench v1.3.2 PHP 8.1.0 + JIT + opcache (no xdebug extension)

| Items count | Memory |       Find | Get Filters (aggregates) | Sort by field | Results Found |
| ----------: | -----: | ---------: | -----------------------: | ------------: | ------------: |
|      10,000 |   ~7Mb | ~0.0007 s. |                ~0.003 s. |    ~0.0003 s. |           907 |
|      50,000 |  ~49Mb |  ~0.002 s. |                ~0.014 s. |    ~0.0009 s. |          4550 |
|     100,000 |  ~98Mb |  ~0.004 s. |                ~0.029 s. |     ~0.002 s. |          8817 |
|     300,000 | ~242Mb |  ~0.013 s. |                ~0.113 s. |     ~0.007 s. |         26891 |
|   1,000,000 | ~812Mb |  ~0.064 s. |                ~0.447 s. |     ~0.037 s. |         90520 |

### v1.3.2 (4.12.2021)
Performance update

Note. Search->find doesn't guarantee sorted result

* Reduced memory allocation calls in ValueFilter.
* Reduced memory allocation calls during filters aggregates.

Bench v1.3.2 PHP 8.1.0 + JIT + opcache (no xdebug extension)

| Items count | Memory |       Find | Get Filters (aggregates) | Sort by field | Results Found |
| ----------: | -----: | ---------: | -----------------------: | ------------: | ------------: |
|      10,000 |   ~7Mb | ~0.0007 s. |                ~0.003 s. |    ~0.0003 s. |           907 |
|      50,000 |  ~49Mb |  ~0.002 s. |                ~0.014 s. |    ~0.0009 s. |          4550 |
|     100,000 |  ~98Mb |  ~0.004 s. |                ~0.029 s. |     ~0.002 s. |          8817 |
|     300,000 | ~242Mb |  ~0.013 s. |                ~0.113 s. |     ~0.007 s. |         26891 |
|   1,000,000 | ~812Mb |  ~0.064 s. |                ~0.447 s. |     ~0.037 s. |         90520 |

Bench v1.3.1 PHP 8.1.0 + JIT + opcache (no xdebug extension)

| Items count | Memory |       Find | Get Filters (aggregates) | Sort by field | Results Found |
| ----------: | -----: | ---------: | -----------------------: | ------------: | ------------: |
|      10,000 |   ~7Mb | ~0.0007 s. |                ~0.003 s. |    ~0.0004 s. |           907 |
|      50,000 |  ~49Mb |  ~0.004 s. |                ~0.016 s. |    ~0.0009 s. |          4550 |
|     100,000 |  ~98Mb |  ~0.007 s. |                ~0.036 s. |     ~0.002 s. |          8817 |
|     300,000 | ~242Mb |  ~0.022 s. |                ~0.135 s. |     ~0.009 s. |         26891 |
|   1,000,000 | ~812Mb |  ~0.095 s. |                ~0.572 s. |     ~0.035 s. |         90520 |



### v1.3.1 (3.12.2021)
Performance update
* Reduced memory allocation calls during filters aggregates.
* RecordId cache added to index->getAllRecords()

Bench v1.3.1 PHP 8.1.0 + JIT + opcache (no xdebug extension)

| Items count | Memory |       Find | Get Filters (aggregates) | Sort by field | Results Found |
| ----------: | -----: | ---------: | -----------------------: | ------------: | ------------: |
|      10,000 |   ~7Mb | ~0.0007 s. |                ~0.003 s. |    ~0.0004 s. |           907 |
|      50,000 |  ~49Mb |  ~0.004 s. |                ~0.016 s. |    ~0.0009 s. |          4550 |
|     100,000 |  ~98Mb |  ~0.007 s. |                ~0.036 s. |     ~0.002 s. |          8817 |
|     300,000 | ~242Mb |  ~0.022 s. |                ~0.135 s. |     ~0.009 s. |         26891 |
|   1,000,000 | ~812Mb |  ~0.095 s. |                ~0.572 s. |     ~0.035 s. |         90520 |

Bench v1.3.0 PHP 8.1.0 + JIT + opcache (no xdebug extension)

| Items count | Memory |       Find | Get Filters (aggregates) | Sort by field | Results Found |
| ----------: | -----: | ---------: | -----------------------: | ------------: | ------------: |
|      10,000 |   ~7Mb | ~0.0007 s. |                ~0.003 s. |    ~0.0004 s. |           907 |
|      50,000 |  ~49Mb |  ~0.003 s. |                ~0.019 s. |    ~0.0009 s. |          4550 |
|     100,000 |  ~98Mb |  ~0.007 s. |                ~0.040 s. |     ~0.002 s. |          8817 |
|     300,000 | ~242Mb |  ~0.022 s. |                ~0.166 s. |     ~0.009 s. |         26891 |
|   1,000,000 | ~812Mb |  ~0.107 s. |                ~0.660 s. |     ~0.035 s. |         90520 |

### v1.3.0 (16.11.2021)
Performance update
* Fixed bug with input records filtration in find method
* Reduced memory allocation and calls of array_flip (FilterInterface changed)
* Sort method optimized (up to 10x faster)
* PHPStan updated to 1.x

Bench v1.3.0 PHP 7.4.25 (no xdebug extension)

| Items count | Memory |       Find | Get Filters (aggregates) | Sort by field | Results Found |
| ----------: | -----: | ---------: | -----------------------: | ------------: | ------------: |
|      10,000 |   ~7Mb | ~0.0004 s. |                ~0.003 s. |    ~0.0002 s. |           907 |
|      50,000 |  ~49Mb |  ~0.003 s. |                ~0.019 s. |    ~0.0008 s. |          4550 |
|     100,000 |  ~98Mb |  ~0.007 s. |                ~0.042 s. |     ~0.002 s. |          8817 |
|     300,000 | ~242Mb |  ~0.021 s. |                ~0.167 s. |     ~0.009 s. |         26891 |
|   1,000,000 | ~812Mb |  ~0.107 s. |                ~0.687 s. |     ~0.036 s. |         90520 |

Bench v1.2.8 PHP 7.4.25 (no xdebug extension)

| Items count | Memory |       Find | Get Filters (aggregates) | Sort by field | Results Found |
| ----------: | -----: | ---------: | -----------------------: | ------------: | ------------: |
|      10,000 |   ~7Mb | ~0.0004 s. |                ~0.003 s. |     ~0.001 s. |           907 |
|      50,000 |  ~49Mb |  ~0.004 s. |                ~0.022 s. |     ~0.007 s. |          4550 |
|     100,000 |  ~98Mb |  ~0.009 s. |                ~0.049 s. |     ~0.015 s. |          8817 |
|     300,000 | ~242Mb |  ~0.026 s. |                ~0.182 s. |     ~0.109 s. |         26891 |
|   1,000,000 | ~812Mb |  ~0.125 s. |                ~0.776 s. |     ~0.472 s. |         90520 |



### v1.2.8 (31.10.2021)
Up to 50% acceleration of aggregates
* Performance optimization for aggregates (getFilters). Reduced the number of passes for filter aggregates.
* PHP 8 performance tests added
* Readme updated with performance test comparison (php7/php8)
### v1.2.7 (07.10.2021)
* Small performance enhancements
* Performance tests update
* Readme.md updated with performance test results
### v1.2.6 (22.07.2021)
* New examples and Demo using from "mens-shoe-prices" DataSet
* Fixed findAcceptableFiltersCount logics for cases with empty filters
* Readme updates

### v1.2.5 (21.05.2021)
* findAcceptableFiltersCount method added, return acceptable filters with values count 

### v1.2.4 (12.05.2021)
* Result Sorter added
* Performance test refactoring

