<?php
/**
 *
 * MIT License
 *
 * Copyright (C) 2020  Kirill Yegorov https://github.com/k-samuel
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

namespace KSamuel\FacetedSearch;

use KSamuel\FacetedSearch\Filter\FilterInterface;

/**
 * Class Search
 * Search in faceted index. Easily handles 100,000 products with 10 properties.
 * @package KSamuel\FacetedSearch
 */
class Search
{
    /**
     * @var Index
     */
    protected $index;

    /**
     * Search constructor.
     * @param Index $index
     */
    public function __construct(Index $index)
    {
        $this->index = $index;
    }

    /**
     * Find records by filters
     * @param array<FilterInterface> $filters
     * @param array<int>|null $inputRecords - list of record id to search in. Use it for limit results
     * @return array<int>
     */
    public function find(array $filters, ?array $inputRecords = null) : array
    {
        if(empty($inputRecords)){
            $inputRecords = null;
        }

        $result = $inputRecords;

        // if no filters passed
        if(empty($filters)){
            $total = $this->index->getAllRecordId();
            if(!empty($inputRecords)){
                return array_intersect($total, $inputRecords);
            }
            return $total;
        }

        /**
         * @var FilterInterface $filter
         */
        foreach ($filters as $filter){
            $indexData = $this->index->getFieldData($filter->getFieldName());
            if(empty($indexData)){
                return [];
            }
            $result = $filter->filterResults($indexData, $result);
        }
        if(empty($result)){
            $result = [];
        }
        return $result;
    }

    /**
     * Find acceptable filter values
     * @param array<FilterInterface> $filters
     * @param array<int> $inputRecords
     * @return array<array>
     */
    public function findAcceptableFilters(array $filters = [], array $inputRecords = []): array
    {
        $result = [];
        $facetsData = $this->index->getData();
        $indexedFilters = [];

        if(!empty($filters)){
            // index filters by field
            foreach ($filters as $filter){
                /** @var FilterInterface $filter */
                $indexedFilters[$filter->getFieldName()] = $filter;
            }
        }

        foreach ($facetsData as $filterName => $filterValues) {
            if(empty($indexedFilters) && empty($inputRecords)){
                $result[$filterName] = array_keys($filterValues);
            }else{
                $filtersCopy = $indexedFilters;
                // do not apply self filtering
                unset($filtersCopy[$filterName]);
                $recordIds = $this->find($filtersCopy, $inputRecords);
                foreach ($filterValues as $filterValue => $data) {
                    if (!empty(array_intersect($data, $recordIds))) {
                        $result[$filterName][] = $filterValue;
                    }
                }
            }
        }
        return $result;
    }
}