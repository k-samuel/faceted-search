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
use KSamuel\FacetedSearch\Index\IndexInterface;
use KSamuel\FacetedSearch\OrderBy\ByField;
use KSamuel\FacetedSearch\Query\AggregationQuery;
use KSamuel\FacetedSearch\Query\SearchQuery;

/**
 * Class Search
 * Search in faceted index. Easily handles 300,000 products with 10 properties.
 * @package KSamuel\FacetedSearch
 */
class Search
{
    /**
     * @var IndexInterface
     */
    protected IndexInterface $index;

    /**
     * Search constructor.
     * @param IndexInterface $index
     */
    public function __construct(IndexInterface $index)
    {
        $this->index = $index;
    }

    /**
     * Find records by filters as list of int
     * @param array<FilterInterface> $filters
     * @param array<int>|null $inputRecords - list of record id to search in. Use it for limit results
     * @return array<int>
     */
    public function find(array $filters, ?array $inputRecords = null): array
    {
        return $this->index->find($filters, $inputRecords);
    }

    /**
     * Find records using Query
     * @param SearchQuery $query
     * @return array<int>
     */
    public function query(SearchQuery $query): array
    {
        return $this->index->query($query);
    }

    /**
     * Aggregation. Find acceptable filter values using AggregationQuery
     *
     * @param AggregationQuery $query
     * @return array<string,array<int|string,int|true>>
     * [
     *   'field1' => [
     *          'value1' => int count | true,  (Depending on AggregationQuery settings)
     *          'value2' => int count | true, 
     *          ...
     *   ],
     *   ...
     * ]
     */
    public function aggregate(AggregationQuery $query): array
    {
        return $this->index->aggregation($query);
    }

    /**
     * Find acceptable filter values
     * @param array<FilterInterface> $filters
     * @param array<int> $inputRecords
     * @return array<string,array<int|string,int|string>>
     * @deprecated use aggregate
     */
    public function findAcceptableFilters(array $filters = [], array $inputRecords = []): array
    {
        return $this->index->aggregate($filters, $inputRecords, false);
    }

    /**
     * Find acceptable filters with values count
     * @param array<FilterInterface> $filters
     * @param array<int> $inputRecords
     * @return array<string,array<int|string,int|string>>
     * @deprecated use aggregate
     */
    public function findAcceptableFiltersCount(array $filters = [], array $inputRecords = []): array
    {
        return $this->index->aggregate($filters, $inputRecords, true);
    }
}
