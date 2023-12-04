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

namespace KSamuel\FacetedSearch\Query;

use KSamuel\FacetedSearch\Filter\FilterInterface;

class AggregationQuery
{
    /**
     * @var array<FilterInterface> $filters
     */
    protected array $filters = [];

    protected bool $needCount = false;
    /**
     * @var array<int> $records
     */
    protected array $records = [];

    protected ?AggregationSort $sort = null;

    /**
     * Self filtering is disabled by default
     * @var boolean
     */
    protected bool $selfFiltering = false;


    public function filter(FilterInterface $filter): self
    {
        $this->filters[] = $filter;
        return $this;
    }
    /**
     *
     * @param array<FilterInterface> $filters
     * @return self
     */
    public function filters(array $filters): self
    {
        foreach ($filters as $item) {
            $this->filters[] = $item;
        }
        return $this;
    }

    public function countItems(bool $count = true): self
    {
        $this->needCount = $count;
        return $this;
    }

    public function getCountItems(): bool
    {
        return $this->needCount;
    }

    /**
     * List of record id to search in. For example list of records id that found by external FullText search.
     * @param array<int> $records
     * @return self
     */
    public function inRecords(array $records): self
    {
        $this->records = $records;
        return $this;
    }

    /**
     * Records getter
     * @return array<int>
     */
    public function getInRecords(): array
    {
        return $this->records;
    }

    /**
     * Filters getter
     * @return array<FilterInterface>
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Sort result fields and values
     * @param int $direction Query\OrderBy::SORT_ASC | Query\OrderBy::SORT_DESC
     * @param int $sortFlags
     *  Sorting type flags:
     *     SORT_REGULAR - compare items normally; the details are described in the comparison operators section
     *     SORT_NUMERIC - compare items numerically
     *     SORT_STRING - compare items as strings
     *     SORT_LOCALE_STRING - compare items as strings, based on the current locale. It uses the locale, which can be changed using setlocale()
     *     SORT_NATURAL - compare items as strings using "natural ordering" like natsort()
     *     SORT_FLAG_CASE - can be combined (bitwise OR) with SORT_STRING or SORT_NATURAL to sort strings case-insensitively
     *
     * @return self
     */
    public function sort(int $direction = AggregationSort::SORT_ASC, int $sortFlags = SORT_REGULAR): self
    {
        $this->sort = new AggregationSort($direction, $sortFlags);
        return $this;
    }

    public function getSort(): ?AggregationSort
    {
        return $this->sort;
    }

    /**
     * Enable/Disable self-filtering, disabled by default.
     * Example:
     * User wants a phone with 32GB memory, checks the box for the desired option (16, [x] 32, 64). 
     * If self-filtering is enabled, then all other options in the interface will disappear and 
     * only 32 will remain. Thus, user will not be able to change his choice.
     * The filter value on a specific field during aggregation is used to filter values only for other fields.
     * Example: the size condition intersects with the brand field data to limit the list of brand variations.
     * @param bool $enabled
     * @return self
     */
    public function selfFiltering(bool $enabled): self
    {
        $this->selfFiltering = $enabled;
        return $this;
    }

    /**
     * Get self-filtering flag
     * @return bool
     */
    public function hasSelfFiltering(): bool
    {
        return $this->selfFiltering;
    }
}
