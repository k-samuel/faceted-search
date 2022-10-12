# Changelog
### v2.1.6 (12.10.2022)
### Bug Fix
* [issue#8](https://github.com/k-samuel/faceted-search/issues/9) Version 2.1.5 does not allow integer names for data fields. Reported by [pixobit](https://github.com/pixobit).

# Changelog
### v2.1.5 (23.09.2022)

### Performance update
Aggregate method now up to 33 % faster.

PHPBench v2.1.5 ArrayIndex PHP 8.1.10 + JIT + opcache (no xdebug extension)

| Items count     | Memory   | Find             | Get Filters (aggregate)  | Get Filters & Count (aggregate)| Sort by field| Results Found    |
|----------------:|---------:|-----------------:|-------------------------:|-------------------------------:|-------------:|-----------------:|
| 10,000          | ~6Mb     | ~0.0004 s.       | ~0.001 s.                | ~0.002 s.                      | ~0.0001 s.   | 907              |
| 50,000          | ~40Mb    | ~0.001 s.        | ~0.005 s.                | ~0.010 s.                      | ~0.0005 s.   | 4550             |
| 100,000         | ~80Mb    | ~0.003 s.        | ~0.016 s.                | ~0.029 s.                      | ~0.001 s.    | 8817             |
| 300,000         | ~189Mb   | ~0.011 s.        | ~0.044 s.                | ~0.091 s                       | ~0.004 s.    | 26891            |
| 1,000,000       | ~657Mb   | ~0.047 s.        | ~0.169 s.                | ~0.333 s.                      | ~0.018 s.    | 90520            |

PHPBench v2.1.5 FixedArrayIndex PHP 8.1.10 + JIT + opcache (no xdebug extension)

| Items count     | Memory   | Find             | Get Filters (aggregate)  | Get Filters & Count (aggregate)| Sort by field| Results Found    |
|----------------:|---------:|-----------------:|-------------------------:|-------------------------------:|-------------:|-----------------:|
| 10,000          | ~2Mb     | ~0.0007 s.       | ~0.001 s.                | ~0.003 s.                      | ~0.0002 s.   | 907              |
| 50,000          | ~12Mb    | ~0.003 s.        | ~0.007 s.                | ~0.018 s.                      | ~0.0009 s.   | 4550             |
| 100,000         | ~23Mb    | ~0.006 s.        | ~0.017 s.                | ~0.040 s.                      | ~0.002 s.    | 8817             |
| 300,000         | ~70Mb    | ~0.020 s.        | ~0.059 s.                | ~0.118 s.                      | ~0.006 s.    | 26891            |
| 1,000,000       | ~233Mb   | ~0.079 s.        | ~0.206 s.                | ~0.448 s.                      | ~0.026 s.    | 90520            |


# Changelog
### v2.1.4 (09.08.2022)

### Performance updates
* aggregate method 2x faster for cases without values count
```php
 $search->findAcceptableFilters()
```
* some optimisations of sorting method

PHPBench v2.1.4 ArrayIndex PHP 8.1.9 + JIT + opcache (no xdebug extension)

| Items count     | Memory   | Find             | Get Filters (aggregate)  | Get Filters & Count (aggregate)| Sort by field| Results Found    |
|----------------:|---------:|-----------------:|-------------------------:|-------------------------------:|-------------:|-----------------:|
| 10,000          | ~6Mb     | ~0.0004 s.       | ~0.001 s.                | ~0.002 s.                      | ~0.0001 s.   | 907              |
| 50,000          | ~40Mb    | ~0.001 s.        | ~0.007 s.                | ~0.013 s.                      | ~0.0005 s.   | 4550             |
| 100,000         | ~80Mb    | ~0.003 s.        | ~0.015 s.                | ~0.028 s.                      | ~0.001 s.    | 8817             |
| 300,000         | ~189Mb   | ~0.012 s.        | ~0.057 s.                | ~0.097 s                       | ~0.004 s.    | 26891            |
| 1,000,000       | ~657Mb   | ~0.047 s.        | ~0.233 s.                | ~0.385 s.                      | ~0.017 s.    | 90520            |

PHPBench v2.1.4 FixedArrayIndex PHP 8.1.9 + JIT + opcache (no xdebug extension)

| Items count     | Memory   | Find             | Get Filters (aggregate)  | Get Filters & Count (aggregate)| Sort by field| Results Found    |
|----------------:|---------:|-----------------:|-------------------------:|-------------------------------:|-------------:|-----------------:|
| 10,000          | ~2Mb     | ~0.0007 s.       | ~0.002 s.                | ~0.005 s.                      | ~0.0002 s.   | 907              |
| 50,000          | ~12Mb    | ~0.003 s.        | ~0.012 s.                | ~0.024 s.                      | ~0.0009 s.   | 4550             |
| 100,000         | ~23Mb    | ~0.006 s.        | ~0.025 s.                | ~0.047 s.                      | ~0.002 s.    | 8817             |
| 300,000         | ~70Mb    | ~0.019 s.        | ~0.083 s.                | ~0.149 s.                      | ~0.006 s.    | 26891            |
| 1,000,000       | ~233Mb   | ~0.077 s.        | ~0.306 s.                | ~0.550 s.                      | ~0.025 s.    | 90520            |


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

| Items count     | Memory   | Find             | Get Filters (aggregates) | Sort by field| Results Found    |
|----------------:|---------:|-----------------:|-------------------------:|-------------:|-----------------:|
| 10,000          | ~6Mb     | ~0.0003 s.       | ~0.002 s.                | ~0.0001 s.   | 907              |
| 50,000          | ~40Mb    | ~0.001 s.        | ~0.013 s.                | ~0.0005 s.   | 4550             |
| 100,000         | ~80Mb    | ~0.003 s.        | ~0.028 s.                | ~0.001 s.    | 8817             |
| 300,000         | ~189Mb   | ~0.011 s.        | ~0.100 s.                | ~0.004 s.    | 26891            |
| 1,000,000       | ~657Mb   | ~0.047 s.        | ~0.387 s.                | ~0.018 s.    | 90520            |

PHPBench v2.1.1 FixedArrayIndex PHP 8.1.0 + JIT + opcache (no xdebug extension)

| Items count     | Memory   | Find             | Get Filters (aggregates) | Sort by field| Results Found    |
|----------------:|---------:|-----------------:|-------------------------:|-------------:|-----------------:|
| 10,000          | ~2Mb     | ~0.0007 s.       | ~0.004 s.                | ~0.0001 s.   | 907              |
| 50,000          | ~12Mb    | ~0.003 s.        | ~0.024 s.                | ~0.0009 s.   | 4550             |
| 100,000         | ~23Mb    | ~0.006 s.        | ~0.049 s.                | ~0.001 s.    | 8817             |
| 300,000         | ~70Mb    | ~0.019 s.        | ~0.151 s.                | ~0.006 s.    | 26891            |
| 1,000,000       | ~233Mb   | ~0.078 s.        | ~0.565 s.                | ~0.024 s.    | 90520            |


### v2.1.0 (06.01.2022)

### Performance update and FixedArrayIndex

FixedArrayIndex is much slower than ArrayIndex but requires significant less memory.

* FixedArrayIndex added
* KSamuel\FacetedSearch\Index is deprecated use KSamuel\FacetedSearch\Index\ArrayIndex instead
* Unit and performance tests for FixedArrayIndex
* Added sorting of filter fields before processing (performance)
* Documentation updated

PHPBench v2.1.0 ArrayIndex PHP 8.1.0 + JIT + opcache (no xdebug extension)

| Items count     | Memory   | Find             | Get Filters (aggregates) | Sort by field| Results Found    |
|----------------:|---------:|-----------------:|-------------------------:|-------------:|-----------------:|
| 10,000          | ~6Mb     | ~0.0003 s.       | ~0.002 s.                | ~0.0001 s.   | 907              |
| 50,000          | ~40Mb    | ~0.001 s.        | ~0.013 s.                | ~0.0005 s.   | 4550             |
| 100,000         | ~80Mb    | ~0.003 s.        | ~0.030 s.                | ~0.001 s.    | 8817             |
| 300,000         | ~189Mb   | ~0.011 s.        | ~0.101 s.                | ~0.005 s.    | 26891            |
| 1,000,000       | ~657Mb   | ~0.049 s.        | ~0.396 s.                | ~0.017 s.    | 90520            |

PHPBench v2.1.0 FixedArrayIndex PHP 8.1.0 + JIT + opcache (no xdebug extension)

| Items count     | Memory   | Find             | Get Filters (aggregates) | Sort by field| Results Found    |
|----------------:|---------:|-----------------:|-------------------------:|-------------:|-----------------:|
| 10,000          | ~2Mb     | ~0.0007 s.       | ~0.006 s.                | ~0.0002 s.   | 907              |
| 50,000          | ~12Mb    | ~0.003 s.        | ~0.027 s.                | ~0.001 s.    | 4550             |
| 100,000         | ~23Mb    | ~0.006 s.        | ~0.057 s.                | ~0.002 s.    | 8817             |
| 300,000         | ~70Mb    | ~0.021 s.        | ~0.188 s.                | ~0.007 s.    | 26891            |
| 1,000,000       | ~233Mb   | ~0.080 s.        | ~0.674 s.                | ~0.032 s.    | 90520            |

### v2.0.3  (30.12.2021)
Performance update

* Filter\ValueFilter optimizations
* Sorter\ByField optimizations
* Search->findRecordsMap optimizations
* PHPBench tests added

PHPBench v2.0.3 PHP 8.1.0 + JIT + opcache (no xdebug extension)

| Items count     | Memory   | Find             | Get Filters (aggregates) | Sort by field| Results Found    |
|----------------:|---------:|-----------------:|-------------------------:|-------------:|-----------------:|
| 10,000          | ~6Mb     | ~0.0003 s.       | ~0.002 s.                | ~0.0001 s.   | 907              |
| 50,000          | ~40Mb    | ~0.001 s.        | ~0.013 s.                | ~0.0006 s.   | 4550             |
| 100,000         | ~80Mb    | ~0.003 s.        | ~0.029 s.                | ~0.001 s.    | 8817             |
| 300,000         | ~189Mb   | ~0.011 s.        | ~0.108 s.                | ~0.005 s.    | 26891            |
| 1,000,000       | ~657Mb   | ~0.052 s.        | ~0.419 s.                | ~0.018 s.    | 90520            |


### v2.0.2 (13.12.2021)
* fix default example of performance tests

### v2.0.1 (16.12.2021)
* fix for PHP 8.1 deprecation of indirect floating point conversion in array keys

### v2.0.0 (13.12.2021)

Reduced Index memory usage.
Backward incompatibility, faceted index should be reindex before using new version of library.

Bench v2.0.0 PHP 8.1.0 + JIT + opcache (no xdebug extension)

| Items count     | Memory   | Find             | Get Filters (aggregates) | Sort by field| Results Found    |
|----------------:|---------:|-----------------:|-------------------------:|-------------:|-----------------:|
| 10,000          | ~6Mb     | ~0.0007 s.       | ~0.004 s.                | ~0.0005 s.   | 907              |
| 50,000          | ~40Mb    | ~0.002 s.        | ~0.014 s.                | ~0.001 s.    | 4550             |
| 100,000         | ~80Mb    | ~0.004 s.        | ~0.028 s.                | ~0.001 s.    | 8817             |
| 300,000         | ~189Mb   | ~0.011 s.        | ~0.104 s.                | ~0.006 s.    | 26891            |
| 1,000,000       | ~657Mb   | ~0.050 s.        | ~0.426 s.                | ~0.030 s.    | 90520            |

Bench v1.3.3 PHP 8.1.0 + JIT + opcache (no xdebug extension)

| Items count     | Memory   | Find             | Get Filters (aggregates) | Sort by field| Results Found    |
|----------------:|---------:|-----------------:|-------------------------:|-------------:|-----------------:|
| 10,000          | ~7Mb     | ~0.0007 s.       | ~0.004 s.                | ~0.0003 s.   | 907              |
| 50,000          | ~49Mb    | ~0.002 s.        | ~0.014 s.                | ~0.0009 s.   | 4550             |
| 100,000         | ~98Mb    | ~0.004 s.        | ~0.028 s.                | ~0.002 s.    | 8817             |
| 300,000         | ~242Mb   | ~0.012 s.        | ~0.112 s.                | ~0.007 s.    | 26891            |
| 1,000,000       | ~812Mb   | ~0.057 s.        | ~0.443 s.                | ~0.034 s.    | 90520            |


### v1.3.4 (13.12.2021)

* bugfix ```$search->findAcceptableFilters([],[1,2,3]);``` returns empty results for empty filters and none empty id list in arguments.
Thanks to [@chrisvidal](https://github.com/chrisvidal) for reporting.

### v1.3.3 (11.12.2021)
Performance update

* Added search optimization for ValueFilter. Filters are sorted before searching to reduce memory allocation

Unfortunately, the filter sequence in previous performance tests was already optimal.

Bench v1.3.3 PHP 8.1.0 + JIT + opcache (no xdebug extension)

| Items count     | Memory   | Find             | Get Filters (aggregates) | Sort by field| Results Found    |
|----------------:|---------:|-----------------:|-------------------------:|-------------:|-----------------:|
| 10,000          | ~7Mb     | ~0.0007 s.       | ~0.004 s.                | ~0.0003 s.   | 907              |
| 50,000          | ~49Mb    | ~0.002 s.        | ~0.014 s.                | ~0.0009 s.   | 4550             |
| 100,000         | ~98Mb    | ~0.004 s.        | ~0.028 s.                | ~0.002 s.    | 8817             |
| 300,000         | ~242Mb   | ~0.012 s.        | ~0.112 s.                | ~0.007 s.    | 26891            |
| 1,000,000       | ~812Mb   | ~0.057 s.        | ~0.443 s.                | ~0.034 s.    | 90520            |

Bench v1.3.2 PHP 8.1.0 + JIT + opcache (no xdebug extension)

| Items count     | Memory   | Find             | Get Filters (aggregates) | Sort by field| Results Found    |
|----------------:|---------:|-----------------:|-------------------------:|-------------:|-----------------:|
| 10,000          | ~7Mb     | ~0.0007 s.       | ~0.003 s.                | ~0.0003 s.   | 907              |
| 50,000          | ~49Mb    | ~0.002 s.        | ~0.014 s.                | ~0.0009 s.   | 4550             |
| 100,000         | ~98Mb    | ~0.004 s.        | ~0.029 s.                | ~0.002 s.    | 8817             |
| 300,000         | ~242Mb   | ~0.013 s.        | ~0.113 s.                | ~0.007 s.    | 26891            |
| 1,000,000       | ~812Mb   | ~0.064 s.        | ~0.447 s.                | ~0.037 s.    | 90520            |

### v1.3.2 (4.12.2021)
Performance update

Note. Search->find doesn't guarantee sorted result

* Reduced memory allocation calls in ValueFilter.
* Reduced memory allocation calls during filters aggregates.

Bench v1.3.2 PHP 8.1.0 + JIT + opcache (no xdebug extension)

| Items count     | Memory   | Find             | Get Filters (aggregates) | Sort by field| Results Found    |
|----------------:|---------:|-----------------:|-------------------------:|-------------:|-----------------:|
| 10,000          | ~7Mb     | ~0.0007 s.       | ~0.003 s.                | ~0.0003 s.   | 907              |
| 50,000          | ~49Mb    | ~0.002 s.        | ~0.014 s.                | ~0.0009 s.   | 4550             |
| 100,000         | ~98Mb    | ~0.004 s.        | ~0.029 s.                | ~0.002 s.    | 8817             |
| 300,000         | ~242Mb   | ~0.013 s.        | ~0.113 s.                | ~0.007 s.    | 26891            |
| 1,000,000       | ~812Mb   | ~0.064 s.        | ~0.447 s.                | ~0.037 s.    | 90520            |

Bench v1.3.1 PHP 8.1.0 + JIT + opcache (no xdebug extension)

| Items count     | Memory   | Find             | Get Filters (aggregates) | Sort by field| Results Found    |
|----------------:|---------:|-----------------:|-------------------------:|-------------:|-----------------:|
| 10,000          | ~7Mb     | ~0.0007 s.       | ~0.003 s.                | ~0.0004 s.   | 907              |
| 50,000          | ~49Mb    | ~0.004 s.        | ~0.016 s.                | ~0.0009 s.   | 4550             |
| 100,000         | ~98Mb    | ~0.007 s.        | ~0.036 s.                | ~0.002 s.    | 8817             |
| 300,000         | ~242Mb   | ~0.022 s.        | ~0.135 s.                | ~0.009 s.    | 26891            |
| 1,000,000       | ~812Mb   | ~0.095 s.        | ~0.572 s.                | ~0.035 s.    | 90520            |



### v1.3.1 (3.12.2021)
Performance update
* Reduced memory allocation calls during filters aggregates.
* RecordId cache added to index->getAllRecords()

Bench v1.3.1 PHP 8.1.0 + JIT + opcache (no xdebug extension)

| Items count     | Memory   | Find             | Get Filters (aggregates) | Sort by field| Results Found    |
|----------------:|---------:|-----------------:|-------------------------:|-------------:|-----------------:|
| 10,000          | ~7Mb     | ~0.0007 s.       | ~0.003 s.                | ~0.0004 s.   | 907              |
| 50,000          | ~49Mb    | ~0.004 s.        | ~0.016 s.                | ~0.0009 s.   | 4550             |
| 100,000         | ~98Mb    | ~0.007 s.        | ~0.036 s.                | ~0.002 s.    | 8817             |
| 300,000         | ~242Mb   | ~0.022 s.        | ~0.135 s.                | ~0.009 s.    | 26891            |
| 1,000,000       | ~812Mb   | ~0.095 s.        | ~0.572 s.                | ~0.035 s.    | 90520            |

Bench v1.3.0 PHP 8.1.0 + JIT + opcache (no xdebug extension)

| Items count     | Memory   | Find             | Get Filters (aggregates) | Sort by field| Results Found    |
|----------------:|---------:|-----------------:|-------------------------:|-------------:|-----------------:|
| 10,000          | ~7Mb     | ~0.0007 s.       | ~0.003 s.                | ~0.0004 s.   | 907              |
| 50,000          | ~49Mb    | ~0.003 s.        | ~0.019 s.                | ~0.0009 s.   | 4550             |
| 100,000         | ~98Mb    | ~0.007 s.        | ~0.040 s.                | ~0.002 s.    | 8817             |
| 300,000         | ~242Mb   | ~0.022 s.        | ~0.166 s.                | ~0.009 s.    | 26891            |
| 1,000,000       | ~812Mb   | ~0.107 s.        | ~0.660 s.                | ~0.035 s.    | 90520            |

### v1.3.0 (16.11.2021)
Performance update
* Fixed bug with input records filtration in find method
* Reduced memory allocation and calls of array_flip (FilterInterface changed)
* Sort method optimized (up to 10x faster)
* PHPStan updated to 1.x

Bench v1.3.0 PHP 7.4.25 (no xdebug extension)

| Items count     | Memory   | Find             | Get Filters (aggregates) | Sort by field| Results Found    |
|----------------:|---------:|-----------------:|-------------------------:|-------------:|-----------------:|
| 10,000          | ~7Mb     | ~0.0004 s.       | ~0.003 s.                | ~0.0002 s.   | 907              |
| 50,000          | ~49Mb    | ~0.003 s.        | ~0.019 s.                | ~0.0008 s.   | 4550             |
| 100,000         | ~98Mb    | ~0.007 s.        | ~0.042 s.                | ~0.002 s.    | 8817             |
| 300,000         | ~242Mb   | ~0.021 s.        | ~0.167 s.                | ~0.009 s.    | 26891            |
| 1,000,000       | ~812Mb   | ~0.107 s.        | ~0.687 s.                | ~0.036 s.    | 90520            |

Bench v1.2.8 PHP 7.4.25 (no xdebug extension)

| Items count     | Memory   | Find             | Get Filters (aggregates) | Sort by field| Results Found    |
|----------------:|---------:|-----------------:|-------------------------:|-------------:|-----------------:|
| 10,000          | ~7Mb     | ~0.0004 s.       | ~0.003 s.                | ~0.001 s.    | 907              |
| 50,000          | ~49Mb    | ~0.004 s.        | ~0.022 s.                | ~0.007 s.    | 4550             |
| 100,000         | ~98Mb    | ~0.009 s.        | ~0.049 s.                | ~0.015 s.    | 8817             |
| 300,000         | ~242Mb   | ~0.026 s.        | ~0.182 s.                | ~0.109 s.    | 26891            |
| 1,000,000       | ~812Mb   | ~0.125 s.        | ~0.776 s.                | ~0.472 s.    | 90520            |



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

