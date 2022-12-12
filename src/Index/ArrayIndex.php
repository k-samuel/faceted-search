<?php

/**
 *
 * MIT License
 *
 * Copyright (C) 2020-2022  Kirill Yegorov https://github.com/k-samuel
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

use KSamuel\FacetedSearch\Filter\FilterInterface;
use KSamuel\FacetedSearch\Filter\ValueFilter;
use KSamuel\FacetedSearch\Indexer\IndexerInterface;
use KSamuel\FacetedSearch\Query\AggregationQuery;
use KSamuel\FacetedSearch\Query\Order;
use KSamuel\FacetedSearch\Query\SearchQuery;


/**
 * Simple faceted index
 * @package KSamuel\FacetedSearch
 */
class ArrayIndex implements IndexInterface
{
    /**
     * Index data
     * @var array<int|string,array<int|string,array<int>>>
     */
    protected array $data = [];
    /**
     * @var array<IndexerInterface>
     */
    protected array $indexers = [];
    /**
     * @var array<int,bool>
     */
    private array $idMapCache = [];

    /**
     * Add record to index
     * @param int $recordId
     * @param array<int|string,array<int,mixed>> $recordValues -  ['fieldName'=>'fieldValue','fieldName2'=>['val1','val2']]
     * @return bool
     */
    public function addRecord(int $recordId, array $recordValues): bool
    {
        $this->resetLocalCache();
        foreach ($recordValues as $fieldName => $values) {
            if (!is_array($values)) {
                $values = [$values];
            }

            $values = array_unique($values);

            if (isset($this->indexers[$fieldName])) {
                if (!isset($this->data[$fieldName])) {
                    $this->data[$fieldName] = [];
                }
                if (!$this->indexers[$fieldName]->add($this->data[$fieldName], $recordId, $values)) {
                    return false;
                }
            } else {
                foreach ($values as $value) {
                    if (is_bool($value)) {
                        $value = (int)$value;
                    }
                    if (is_float($value)) {
                        $value = (string)$value;
                    }
                    $this->data[$fieldName][$value][] = $recordId;
                }
            }
        }
        return true;
    }

    protected function resetLocalCache(): void
    {
        $this->idMapCache = [];
    }

    /**
     * Get facet data.
     * @return array<int|string,array<int|string,array<int>|\SplFixedArray<int>>>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Set index data. Can be used for restoring from DB
     * @param array<int|string,array<int|string,array<int>>> $data
     */
    public function setData(array $data): void
    {
        $this->resetLocalCache();
        $this->data = $data;
    }

    /**
     * Get field data section from index
     * @param string $fieldName
     * @return array<int|string,array<int>|\SplFixedArray<int>>
     */
    public function getFieldData(string $fieldName): array
    {
        return $this->data[$fieldName] ?? [];
    }

    /**
     * Get all records from index
     * @return array<int>
     */
    public function getAllRecordId(): array
    {
        return array_keys($this->getAllREcordIdMap());
    }

    /**
     * Get all records from index as map [$id1=>true,...]
     * @return array<int,bool>
     */
    public function getAllRecordIdMap(): array
    {
        if (!empty($this->idMapCache)) {
            return $this->idMapCache;
        }

        $result = [];
        foreach ($this->data as $values) {
            foreach ($values as $list) {
                foreach ($list as $v) {
                    $result[$v] = true;
                }
            }
        }
        /**
         * @var array<int,bool> $result
         */

        $this->idMapCache = $result;
        return $result;
    }

    /**
     * Add specialized indexer for field
     * @param string $fieldName
     * @param IndexerInterface $indexer
     */
    public function addIndexer(string $fieldName, IndexerInterface $indexer): void
    {
        $this->indexers[$fieldName] = $indexer;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return int
     */
    public function getRecordsCount(string $field, $value): int
    {
        if (!isset($this->data[$field][$value])) {
            return 0;
        }
        return count($this->data[$field][$value]);
    }

    /**
     * Check if field exists
     * @param string $fieldName
     * @return bool
     */
    public function hasField(string $fieldName): bool
    {
        return isset($this->data[$fieldName]);
    }

    /**
     * Find records by filters as list of int
     * @param array<FilterInterface> $filters
     * @param array<int>|null $inputRecords - list of record id to search in. Use it for limit results
     * @return array<int> 
     * @deprecated use query
     */
    public function find(array $filters, ?array $inputRecords = null): array
    {
        $input = [];
        if (!empty($inputRecords)) {
            $input = $this->mapInputArray($inputRecords);
        }

        // Aggregates optimization for value filters.
        // The fewer elements after the first filtering, the fewer data copies and memory allocations in iterations
        if (empty($inputRecords) && count($filters) > 1) {
            $filters = $this->sortFiltersByCount($filters);
        }

        return array_keys($this->findRecordsMap($filters, $input));
    }

    /**
     * Find records using Query
     * @param SearchQuery $query
     * @return array<int>
     */
    public function query(SearchQuery $query): array
    {
        $inputRecords =  $query->getInRecords();
        $filters = $query->getFilters();
        $order = $query->getOrder();

        if (!empty($inputRecords)) {
            $inputRecords = $this->mapInputArray($inputRecords);
        }

        // Aggregates optimization for value filters.
        // The fewer elements after the first filtering, the fewer data copies and memory allocations in iterations
        if (empty($inputRecords) && count($filters) > 1) {
            $filters = $this->sortFiltersByCount($filters);
        }

        $map = $this->findRecordsMap($filters, $inputRecords);

        if (!empty($order)) {
            return $this->sortResults($map, $order);
        }

        return array_keys($map);
    }

    /**
     * Find acceptable filter values
     * @param array<FilterInterface> $filters
     * @param array<int> $inputRecords
     * @param bool $countValues
     * @return array<string,array<int|string,int|string>>
     * @deprecated use aggregation (not replaced with aggregation has different result format)
     */
    public function aggregate(array $filters = [], array $inputRecords = [], bool $countValues = false): array
    {
        $input = [];

        if (!empty($inputRecords)) {
            $input = $this->mapInputArray($inputRecords);
        }

        // Aggregates optimization for value filters.
        // The fewer elements after the first filtering, the fewer data copies and memory allocations in iterations
        if (empty($inputRecords) && count($filters) > 1) {
            $filters = $this->sortFiltersByCount($filters);
        }

        $result = [];
        $indexedFilters = [];
        $filteredRecords = [];

        $resultCache = [];

        if (!empty($filters)) {
            // Aggregates optimization for value filters.
            // The fewer elements after the first filtering, the fewer data copies and memory allocations in iterations
            if (count($filters) > 1) {
                $filters = $this->sortFiltersByCount($filters);
            }
            // index filters by field
            foreach ($filters as $filter) {
                $name = $filter->getFieldName();
                $indexedFilters[$name] = $filter;
                $resultCache[$name] = $this->findRecordsMap([$filter], $input);
            }
            // merge results
            $filteredRecords = $this->mergeFilters($resultCache);
        } elseif (!empty($input)) {
            $filteredRecords = $this->findRecordsMap([], $input);
        }

        $resultCacheCount = count($resultCache);

        foreach ($this->data as $filterName => $filterValues) {
            /**
             * @var string $filterName
             */
            if (empty($indexedFilters) && empty($input)) {
                if ($countValues) {
                    // need to count values
                    foreach ($filterValues as $key => $list) {
                        $result[$filterName][$key] = count($list);
                    }
                } else {
                    $result[$filterName] = array_keys($filterValues);
                }
                continue;
            }

            // do not apply self filtering
            if (isset($resultCache[$filterName])) {
                // count of cached filters must be > 1 (1 filter will be skipped by field name)
                if ($resultCacheCount > 1) {
                    // optimization with cache of findRecordsMap
                    $recordIds = $this->mergeFilters($resultCache, $filterName);
                } else {
                    $recordIds = $this->findRecordsMap([], $input);
                }
            } else {
                $recordIds = $filteredRecords;
            }

            foreach ($filterValues as $filterValue => $data) {
                if ($countValues) {
                    // need to count values
                    /**
                     * @var array<int,int> $data
                     */
                    $intersect = $this->getIntersectMapCount($data, $recordIds);

                    if ($intersect === 0) {
                        continue;
                    }

                    $result[$filterName][$filterValue] = $intersect;
                    // results without count
                } elseif ($this->hasIntersectIntMap($data, $recordIds)) {
                    $result[$filterName][] = $filterValue;
                }
            }
        }
        return $result;
    }

    /**
     * Find acceptable filter values
     * @param AggregationQuery $query
     * @return array<int|string,array<int|string,int|true>>
     */
    public function aggregation(AggregationQuery $query): array
    {
        $input = $query->getInRecords();
        $filters = $query->getFilters();
        $countValues = $query->getCountItems();

        // Return all values from index if filters and input is not set
        if (empty($filters) && empty($input)) {
            if ($countValues) {
                return $this->getValuesCount();
            }
            return $this->getValues();
        }

        if (!empty($input)) {
            $input = $this->mapInputArray($input);
        }

        $result = [];
        $indexedFilters = [];
        $filteredRecords = [];
        $resultCache = [];

        if (!empty($filters)) {
            // Aggregates optimization for value filters.
            // The fewer elements after the first filtering, the fewer data copies and memory allocations in iterations
            if (count($filters) > 1) {
                $filters = $this->sortFiltersByCount($filters);
            }
            // index filters by field
            foreach ($filters as $filter) {
                $name = $filter->getFieldName();
                $indexedFilters[$name] = $filter;
                $resultCache[$name] = $this->findRecordsMap([$filter], $input);
            }
            // merge results
            $filteredRecords = $this->mergeFilters($resultCache);
        } elseif (!empty($input)) {
            $filteredRecords = $this->findRecordsMap([], $input);
        }

        $resultCacheCount = count($resultCache);

        foreach ($this->data as $filterName => $filterValues) {
            /**
             * @var string $filterName
             */

            // do not apply self filtering
            if (isset($resultCache[$filterName])) {
                // count of cached filters must be > 1 (1 filter will be skipped by field name)
                if ($resultCacheCount > 1) {
                    // optimization with cache of findRecordsMap
                    $recordIds = $this->mergeFilters($resultCache, $filterName);
                } else {
                    $recordIds = $this->findRecordsMap([], $input);
                }
            } else {
                $recordIds = $filteredRecords;
            }

            if ($countValues) {
                foreach ($filterValues as $filterValue => $data) {
                    /**
                     * @var array<int,int> $data
                     */
                    $intersect = $this->getIntersectMapCount($data, $recordIds);

                    if ($intersect === 0) {
                        continue;
                    }
                    $result[$filterName][$filterValue] = $intersect;
                }
            } else {
                foreach ($filterValues as $filterValue => $data) {
                    if ($this->hasIntersectIntMap($data, $recordIds)) {
                        $result[$filterName][$filterValue] = true;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @return array<int|string,array<string|int,true>>
     */
    protected function getValues(): array
    {
        $result = [];
        foreach ($this->data as $filterName => $filterValues) {
            foreach ($filterValues as $key => $info) {
                $result[$filterName][$key] = true;
            }
        }
        return $result;
    }
    /**
     * @return array<int|string,array<string|int,int>>
     */
    protected function getValuesCount(): array
    {
        $result = [];
        foreach ($this->data as $filterName => $filterValues) {
            foreach ($filterValues as $key => $list) {
                $result[$filterName][$key] = count($list);
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
     * Find records by filters as array map [$id1=>true, $id2=>true, ...]
     * @param array<FilterInterface> $filters
     * @param array<int,bool> $inputRecords
     * @return array<int,bool>
     */
    private function findRecordsMap(array $filters, array $inputRecords): array
    {

        // if no filters passed
        if (empty($filters)) {
            return $this->findInput($inputRecords);
        }

        /**
         * @var FilterInterface $filter
         */
        foreach ($filters as $filter) {
            $indexData = $this->data[$filter->getFieldName()] ?? [];
            if (empty($indexData)) {
                return [];
            }

            $filter->filterInput($indexData, $inputRecords);

            if (empty($inputRecords)) {
                return [];
            }
        }

        return $inputRecords;
    }
    /**
     * Find records without filters as array map [$id1=>true, $id2=>true, ...]
     * @param array<int,bool> $inputRecords
     * @return array<int,bool>
     */
    private function findInput(array $inputRecords): array
    {
        $total = $this->getAllRecordIdMap();
        if (!empty($inputRecords)) {
            return array_intersect_key($total, $inputRecords);
        }
        /**
         * @var array<int,bool> $total
         */
        return $total;
    }
    /**
     * @param array<int,int> $a
     * @param array<int,bool> $b
     * @return int
     */
    protected function getIntersectMapCount(array $a, array $b): int
    {
        $intersectLen = 0;

        foreach ($a as $key) {
            if (isset($b[$key])) {
                $intersectLen++;
            }
        }

        return $intersectLen;
    }

    /**
     * @param array<int,int> $a
     * @param array<int,bool> $b
     * @return bool
     */
    protected function hasIntersectIntMap(array $a, array $b): bool
    {
        foreach ($a as $key) {
            if (isset($b[$key])) {
                return true;
            }
        }
        return false;
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
     * Sort filters by minimum values count
     * Used for aggregates optimization (for ValueFilter)
     * @param array<FilterInterface> $filters
     * @return array<FilterInterface>
     */
    private function sortFiltersByCount(array $filters): array
    {
        $counts = [];
        foreach ($filters as $index => $filter) {
            if (!$filter instanceof ValueFilter) {
                $counts[$index] = PHP_INT_MAX;
                continue;
            }
            /**
             * @var ValueFilter $filter
             */
            $fieldName = $filter->getFieldName();

            if (!isset($this->data[$fieldName])) {
                $counts[$index] = 0;
                continue;
            }

            /**
             * @var array<int,mixed> $filterValues
             */
            $filterValues = $filter->getValue();

            $filterValuesCount = [];
            $valuesInFilter = count($filterValues);
            foreach ($filterValues as $value) {
                $cnt = $this->getRecordsCount($fieldName, $value);
                if ($valuesInFilter > 1) {
                    $filterValuesCount[$value] = $cnt;
                }

                if (!isset($counts[$index])) {
                    $counts[$index] = $cnt;
                    continue;
                }

                if ($counts[$index] > $cnt) {
                    $counts[$index] = $cnt;
                }
            }

            if ($valuesInFilter > 1) {
                // sort filter values by records count
                asort($filterValuesCount);
                // update filers with new values order
                $filter->setValue(array_keys($filterValuesCount));
            }
        }
        asort($counts);
        $result = [];
        foreach ($counts as $index => $count) {
            $result[] = $filters[$index];
        }

        return $result;
    }
    /**
     * Sort results by field value
     * @param array<int,bool> & $resultsMap
     * @param Order $order
     * @return array<int>
     */
    protected function sortResults(array &$resultsMap, Order $order): array
    {
        $field = $order->getField();

        if (!isset($this->data[$field]) || empty($this->data[$field])) {
            return [];
        }

        $values = array_keys($this->data[$field]);

        if ($order->getDirection() === Order::SORT_ASC) {
            sort($values, $order->getSortFlags());
        } else {
            rsort($values, $order->getSortFlags());
        }

        $sorted = [];
        foreach ($values as $value) {
            $records = &$this->data[$field][$value];

            // inline intersection - intersectIntMap
            /**
             * @var array<int>|\SplFixedArray<int> $records
             */
            if (is_array($records)) {
                foreach ($records as $key) {
                    /**
                     * @var int $key
                     */
                    if (isset($resultsMap[$key])) {
                        $sorted[] = $key;
                        // already sorted
                        unset($resultsMap[$key]);
                    }
                }
            } else {
                // Performance patch SplFixedArray index access is faster than iteration
                $count = count($records);
                for ($i = 0; $i < $count; $i++) {
                    /**
                     * @var int $key
                     */
                    $key = $records[$i];
                    if (isset($resultsMap[$key])) {
                        $sorted[] = $key;
                        // already sorted
                        unset($resultsMap[$key]);
                    }
                }
            }
        }
        return $sorted;
    }
}
