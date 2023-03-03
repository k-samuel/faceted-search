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

namespace KSamuel\FacetedSearch\Index;

use KSamuel\FacetedSearch\Filter\FilterInterface;
use KSamuel\FacetedSearch\Index\Storage\Scanner;
use KSamuel\FacetedSearch\Index\Storage\StorageInterface;
use KSamuel\FacetedSearch\Indexer\IndexerInterface;
use KSamuel\FacetedSearch\Query\AggregationQuery;
use KSamuel\FacetedSearch\Query\SearchQuery;

/**
 * Simple faceted index
 * @package KSamuel\FacetedSearch
 */
interface IndexInterface
{
    /**
     * Find acceptable filter values. Note that the format of the result has changed compared to the "aggregate" method
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
    public function aggregate(AggregationQuery $query): array;

    /**
     * Find records using Query
     * @param SearchQuery $query
     * @return array<int>
     */
    public function query(SearchQuery $query): array;

    /**
     * Set time profiler (debug and bench)
     * @param Profile $profile
     * @return void
     */
    public function setProfiler(Profile $profile): void;

    /**
     * Get index storage
     * @return StorageInterface
     */
    public function getStorage(): StorageInterface;

    /**
     * Get index scanner
     * @return Scanner
     */
    public function getScanner(): Scanner;

    /**
     * Get records count
     * @return integer
     */
    public function getCount(): int;
    /**
     * Load saved data
     * @param array<mixed> $data
     * @return void
     */
    public function setData(array $data);
    /**
     * Export facet index data.
     * @return array<int|string,array<int|string,array<int>>>
     */
    public function export(): array;
    /**
     * Optimize index structure
     * @return void
     */
    public function optimize(): void;
}
