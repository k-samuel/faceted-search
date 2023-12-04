<?php

/**
 *
 * MIT License
 *
 * Copyright (C) 2020-2023  Kirill Yegorov https://github.com/k-samuel
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 */

declare(strict_types=1);

namespace KSamuel\FacetedSearch\Index;

use KSamuel\FacetedSearch\Filter\ExcludeFilterInterface;
use KSamuel\FacetedSearch\Filter\FilterInterface;
use KSamuel\FacetedSearch\Index\Sort\AggregationResults;
use KSamuel\FacetedSearch\Index\Sort\Filters;

use KSamuel\FacetedSearch\Index\Storage\StorageInterface;
use KSamuel\FacetedSearch\Index\Storage\Scanner;
use KSamuel\FacetedSearch\Query\AggregationQuery;
use KSamuel\FacetedSearch\Index\Intersection\IntersectionInterface;
use KSamuel\FacetedSearch\Index\Sort\QueryResultsInterface;
use KSamuel\FacetedSearch\Query\SearchQuery;




/**
 * Simple faceted index
 * @package KSamuel\FacetedSearch
 */
class Index implements IndexInterface
{
    private StorageInterface $storage;
    private Filters $filterSort;
    private AggregationResults $aggregationSort;
    private QueryResultsInterface $querySort;
    private Scanner $scanner;
    private IntersectionInterface $intersection;

    private ?Profile $profiler = null;

    public function __construct(
        StorageInterface $storage,
        Filters $filterSort,
        AggregationResults $aggregationSort,
        QueryResultsInterface $querySort,
        Scanner $scanner,
        IntersectionInterface $intersection
    ) {
        $this->storage = $storage;
        $this->filterSort = $filterSort;
        $this->aggregationSort = $aggregationSort;
        $this->querySort = $querySort;
        $this->scanner = $scanner;
        $this->intersection = $intersection;
    }

    /**
     * Find records using Query
     * @param SearchQuery $query
     * @return array<int>
     */
    public function query(SearchQuery $query): array
    {
        $inputRecords =  $query->getInRecords();
        $filterList = $query->getFilters();
        $filters = [];
        $exceptFilters = [];

        foreach ($filterList as $item) {
            if ($item instanceof ExcludeFilterInterface) {
                $exceptFilters[] = $item;
            } else {
                $filters[] = $item;
            }
        }
        $order = $query->getOrder();

        if (!empty($inputRecords)) {
            $inputRecords = $this->mapInputArray($inputRecords);
        }

        // Aggregates optimization for value filters.
        // The fewer elements after the first filtering, the fewer data copies and memory allocations in iterations
        if (empty($inputRecords) && count($filters) > 1) {
            $filters = $this->filterSort->byCount($this->storage, $filters);
        }

        $excludeMap = [];
        if (!empty($exceptFilters)) {
            $this->scanner->findExcludeRecordsMap($this->storage, $exceptFilters, $excludeMap);
        }

        $map = $this->scanner->findRecordsMap($this->storage, $filters, $inputRecords, $excludeMap);

        if (!empty($order)) {
            if ($this->profiler != null) {
                $t = microtime(true);
                $result = $this->querySort->sort($this->storage, $map, $order);
                $this->profiler->setSortingTime(microtime(true) - $t);
                return $result;
            } else {
                return $this->querySort->sort($this->storage, $map, $order);
            }
        }

        return array_keys($map);
    }

    /**
     * Find acceptable filter values
     * @param AggregationQuery $query
     * @return array<int|string,array<int|string,int|true>>
     */
    public function aggregate(AggregationQuery $query): array
    {
        $input = $query->getInRecords();

        $filterList = $query->getFilters();
        $filters = [];
        $exceptFilters = [];

        foreach ($filterList as $item) {
            if ($item instanceof ExcludeFilterInterface) {
                $exceptFilters[] = $item;
            } else {
                $filters[] = $item;
            }
        }

        $countValues = $query->getCountItems();
        $sort = $query->getSort();


        $excludeMap = [];
        if (!empty($exceptFilters)) {
            $this->scanner->findExcludeRecordsMap($this->storage, $exceptFilters, $excludeMap);
        }

        // Return all values from index if filters and input is not set
        if (empty($filters) && empty($input)) {

            if ($countValues) {
                $result = $this->getValuesCount($excludeMap);
            } else {
                $result = $this->getValues($excludeMap);
            }

            if ($sort) {
                $this->aggregationSort->sort($sort, $result);
            }
            return $result;
        }

        if (!empty($input)) {
            $input = $this->mapInputArray($input);
        }

        $filteredRecords = [];
        $resultCache = [];

        if (!empty($filters)) {
            // Aggregates optimization for value filters.
            // The fewer elements after the first filtering, the fewer data copies and memory allocations in iterations
            if (count($filters) > 1) {
                $filters = $this->filterSort->byCount($this->storage, $filters);
            }
            // index filters by field
            foreach ($filters as $filter) {
                $name = $filter->getFieldName();
                $resultCache[$name] = $this->scanner->findRecordsMap($this->storage, [$filter], $input, $excludeMap);
            }

            // merge results
            $filteredRecords = $this->mergeFilters($resultCache);
        } elseif (!empty($input)) {
            $filteredRecords = $this->scanner->findRecordsMap($this->storage, [], $input, $excludeMap);
        }

        // intersect index values and filtered records
        $result = $this->aggregationScan(
            $resultCache,
            $filteredRecords,
            $countValues,
            $input,
            $excludeMap,
            $query->hasSelfFiltering(),
            $filters
        );

        if ($sort !== null) {
            $this->aggregationSort->sort($sort, $result);
        }
        return $result;
    }
    /**
     * @param array<int|string,array<int,bool>> $resultCache
     * @param array<int,bool> $filteredRecords
     * @param bool $countRecords
     * @param array<int,bool> $input
     * @param array<int,bool> $exclude
     * @param bool $selfFiltering
     * @param array<FilterInterface> $filters
     * @return array<int|string,array<int|string,int|true>>
     */
    private function aggregationScan(
        array $resultCache,
        array $filteredRecords,
        bool $countRecords,
        array $input,
        array $exclude,
        bool $selfFiltering,
        array $filters
    ): array {
        $result = [];
        $cacheCount = count($resultCache);

        $indexedFilters = [];
        foreach ($filters as $filter) {
            $indexedFilters[$filter->getFieldName()] = $filter;
        }

        /**
         * @var array<int|string,array<int>> $filterValues
         */
        foreach ($this->scanner->scan($this->storage) as $filterName => $filterValues) {

            $needSelfFiltering = false;
            if ($selfFiltering != false || (isset($indexedFilters[$filterName]) && $indexedFilters[$filterName]->hasSelfFiltering())) {
                $needSelfFiltering = true;
            }

            /**
             * @var string $filterName
             */
            if (isset($resultCache[$filterName])) {
                // count of cached filters must be > 1 (1 filter will be skipped by field name)
                if ($cacheCount > 1) {
                    // optimization with cache of findRecordsMap
                    // do not apply self filtering
                    if ($needSelfFiltering) {
                        $skipKey = null;
                    } else {
                        $skipKey = $filterName;
                    }

                    $recordIds = $this->mergeFilters($resultCache, $skipKey);
                } else {
                    // Selecting a self-filtering scenario 
                    if ($needSelfFiltering) {
                        $recordIds = $this->scanner->findRecordsMap($this->storage, $filters, $input, $exclude);
                    } else {
                        $recordIds = $this->scanner->findRecordsMap($this->storage, [], $input, $exclude);
                    }
                }
            } else {
                $recordIds = $filteredRecords;
            }

            foreach ($filterValues as $filterValue => $data) {

                if ($countRecords) {
                    $intersect = $this->intersection->getIntersectMapCount($data, $recordIds);
                    if ($intersect === 0) {
                        continue;
                    }
                    $result[$filterName][$filterValue] = $intersect;
                    continue;
                }

                if ($this->intersection->hasIntersectIntMap($data, $recordIds)) {
                    $result[$filterName][$filterValue] = true;
                }
            }
        }
        return $result;
    }

    /**
     * @param array<int,bool> $excludeMap
     * @return array<int|string,array<string|int,true>>
     */
    protected function getValues(array $excludeMap): array
    {
        $result = [];
        if (empty($excludeMap)) {
            /**
             * @var array<int|sting,array<int>> $filterValues
             */
            foreach ($this->scanner->scan($this->storage) as $filterName => $filterValues) {
                foreach ($filterValues as $key => $info) {
                    $result[$filterName][$key] = true;
                }
            }
        } else {
            /**
             * @var array<int|sting,array<int>> $filterValues
             */
            foreach ($this->scanner->scan($this->storage) as $filterName => $filterValues) {
                foreach ($filterValues as $key => $info) {
                    foreach ($info as $value) {
                        if (!isset($excludeMap[$value])) {
                            $result[$filterName][$key] = true;
                            continue;
                        }
                    }
                }
            }
        }
        return $result;
    }
    /**
     * @param array<int,bool> $excludeMap
     * @return array<int|string,array<string|int,int>>
     */
    protected function getValuesCount(array $excludeMap): array
    {
        $result = [];
        if (empty($excludeMap)) {
            /**
             * @var array<int|sting,array<int>> $filterValues
             */
            foreach ($this->scanner->scan($this->storage) as $filterName => $filterValues) {
                foreach ($filterValues as $key => $list) {
                    $result[$filterName][$key] = count($list);
                }
            }
        } else {
            /**
             * @var array<int|sting,array<int>> $filterValues
             */
            foreach ($this->scanner->scan($this->storage) as $filterName => $filterValues) {
                foreach ($filterValues as $key => $list) {
                    $count = 0;
                    foreach ($list as $value) {
                        if (!isset($excludeMap[$value])) {
                            $count++;
                        }
                    }
                    $result[$filterName][$key] = $count;
                }
            }
        }
        return $result;
    }

    /**
     * @param array<mixed,array<int,bool>> $maps
     * @param string|int|null $skipKey user defined filter name
     * @return array<int,bool>
     */
    private function mergeFilters(array $maps, $skipKey = null): array
    {
        $result = [];
        $start = true;

        foreach ($maps as $key => $map) {
            if ($skipKey !== null && $key === $skipKey) {
                continue;
            }

            if ($start) {
                $result = $map;
                $start = false;
                continue;
            }

            foreach ($result as $k => $v) {
                if (!isset($map[$k])) {
                    unset($result[$k]);
                }
            }
        }
        return $result;
    }

    /**
     * @param array<int> $inputRecords
     * @return array<int,bool>
     */
    private function mapInputArray(array $inputRecords): array
    {
        $input = [];
        foreach ($inputRecords as $v) {
            $input[$v] = true;
        }
        return $input;
    }

    /**
     * Get count of unique records (ids)
     * @return int
     */
    public function getCount(): int
    {
        return count($this->scanner->getAllRecordIdMap($this->storage));
    }

    /**
     * Set time profiler (debug and bench)
     * @param Profile $profile
     * @return void
     */
    public function setProfiler(Profile $profile): void
    {
        $this->profiler = $profile;
    }

    /**
     * Get index storage
     * @return StorageInterface
     */
    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    /**
     * Get index scanner
     * @return Scanner
     */
    public function getScanner(): Scanner
    {
        return $this->scanner;
    }

    /**
     * Load saved data
     * @param array<mixed> $data
     * @return void
     */
    public function setData(array $data): void
    {
        $this->storage->setData($data);
    }

    /**
     * Export facet index data.
     * @return array<int|string,array<int|string,array<int>>>
     */
    public function export(): array
    {
        return $this->storage->export();
    }

    /**
     * Optimize index structure
     * @return void
     */
    public function optimize(): void
    {
        $this->storage->optimize();
    }
}
