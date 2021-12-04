# Changelog

### v1.3.1 (3.12.2021)
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
* New examples and Demo using "mens-shoe-prices" DataSet
* Fixed findAcceptableFiltersCount logics for cases with empty filters
* Readme updates

### v1.2.5 (21.05.2021)
* findAcceptableFiltersCount method added, return acceptable filters with values count 

### v1.2.4 (12.05.2021)
* Result Sorter added
* Performance test refactoring

