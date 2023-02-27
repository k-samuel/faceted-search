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

use KSamuel\FacetedSearch\Filter\FilterInterface;

use KSamuel\FacetedSearch\Index\Sort\AggregationResults;
use KSamuel\FacetedSearch\Index\Sort\Filters;
use KSamuel\FacetedSearch\Index\Sort\QueryResults;
use KSamuel\FacetedSearch\Index\Storage\StorageInterface;
use KSamuel\FacetedSearch\Index\Storage\Scanner;
use KSamuel\FacetedSearch\Indexer\IndexerInterface;
use KSamuel\FacetedSearch\Query\AggregationQuery;
use KSamuel\FacetedSearch\Index\Intersection\IntersectionInterface;

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
    private QueryResults $querySort;
    private Scanner $scanner;
    private IntersectionInterface $intersection;

    public function __construct(
        StorageInterface $storage,
        Filters $filterSort,
        AggregationResults $aggregationSort,
        QueryResults $querySort,
        Scanner $scanner,
        IntersectionInterface $intersection,
    ) {
        $this->storage = $storage;
        $this->filterSort = $filterSort;
        $this->aggregationSort = $aggregationSort;
        $this->querySort = $querySort;
        $this->scanner = $scanner;
        $this->intersection = $intersection;
    }

    /**
     * Add record to index
     * @param int $recordId
     * @param array<int|string,array<int,mixed>> $recordValues -  ['fieldName'=>'fieldValue','fieldName2'=>['val1','val2']]
     * @return bool
     */
    public function addRecord(int $recordId, array $recordValues): bool
    {
        return $this->storage->addRecord($recordId, $recordValues);
    }

    /**
     * Get facet data.
     * @return array<int|string,array<int|string,array<int>>>>
     */
    public function export(): array
    {
        return $this->storage->export();
    }

    /**
     * Set index data. Can be used for restoring from DB
     * @param array<mixed> $data
     */
    public function load(array $data): void
    {
        $this->storage->setData($data);
    }

    // /**
    //  * Get all records from index
    //  * @return array<int>
    //  */
    // public function getAllRecordId(): array
    // {
    //     return array_keys($this->getAllREcordIdMap());
    // }



    /**
     * Add specialized indexer for field
     * @param string $fieldName
     * @param IndexerInterface $indexer
     */
    public function addIndexer(string $fieldName, IndexerInterface $indexer): void
    {
        $this->storage->addIndexer($fieldName, $indexer);
    }

    /**
     * Check if field exists
     * @param string $fieldName
     * @return bool
     */
    public function hasField(string $fieldName): bool
    {
        return $this->storage->hasField($fieldName);
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
            $filters = $this->filterSort->byCount($this->storage, $filters);
        }

        $map = $this->scanner->findRecordsMap($this->storage, $filters, $inputRecords);

        if (!empty($order)) {
            return $this->querySort->sort($this->storage, $map, $order);
        }

        return array_keys($map);
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
        $sort = $query->getSort();

        // Return all values from index if filters and input is not set
        if (empty($filters) && empty($input)) {

            if ($countValues) {
                $result = $this->getValuesCount();
            } else {
                $result = $this->getValues();
            }

            if ($sort) {
                $this->aggregationSort->sort($sort, $result);
            }
            return $result;
        }

        if (!empty($input)) {
            $input = $this->mapInputArray($input);
        }

        $result = [];
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
                $resultCache[$name] = $this->scanner->findRecordsMap($this->storage, [$filter], $input);
            }
            // merge results
            $filteredRecords = $this->mergeFilters($resultCache);
        } elseif (!empty($input)) {
            $filteredRecords = $this->scanner->findRecordsMap($this->storage, [], $input);
        }

        $resultCacheCount = count($resultCache);

        foreach ($this->scanner->scan($this->storage) as $filterName => $filterValues) {
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
                    $recordIds = $this->scanner->findRecordsMap($this->storage, [], $input);
                }
            } else {
                $recordIds = $filteredRecords;
            }

            foreach ($filterValues as $filterValue => $data) {
                if ($countValues) {
                    $intersect = $this->intersection->getIntersectMapCount($data, $recordIds);

                    if ($intersect === 0) {
                        continue;
                    }
                    $result[$filterName][$filterValue] = $intersect;
                } elseif ($this->intersection->hasIntersectIntMap($data, $recordIds)) {
                    $result[$filterName][$filterValue] = true;
                }
            }
        }
        if ($sort !== null) {
            $this->aggregationSort->sort($sort, $result);
        }
        return $result;
    }


    /**
     * @return array<int|string,array<string|int,true>>
     */
    protected function getValues(): array
    {
        $result = [];
        /**
         * @var array<int|sting,array<int>> $filterValues
         */
        foreach ($this->scanner->scan($this->storage) as $filterName => $filterValues) {
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
        /**
         * @var array<int|sting,array<int>> $filterValues
         */
        foreach ($this->scanner->scan($this->storage) as $filterName => $filterValues) {
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
     * Optimize index data
     * @return void
     */
    public function optimize(): void
    {
        $this->storage->optimize();
    }

    /**
     * Delete record from index
     * @param int $recordId
     * @return bool - success flag
     */
    public function deleteRecord(int $recordId): bool
    {
        return $this->storage->deleteRecord($recordId);
    }

    /**
     * Update record data
     * @param int $recordId
     * @param array<int|string,array<int,mixed>> $recordValues -  ['fieldName'=>'fieldValue','fieldName2'=>['val1','val2']]
     * @return bool - success flag
     */
    public function replaceRecord(int $recordId, array $recordValues): bool
    {
        return $this->storage->replaceRecord($recordId, $recordValues);
    }

    /**
     * Get count of unique records (ids)
     * @return int
     */
    public function getRecordsCount(): int
    {
        return count($this->scanner->getAllRecordIdMap($this->storage));
    }
}
