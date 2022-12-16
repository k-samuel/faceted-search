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
use KSamuel\FacetedSearch\Query\Order;

class SearchQuery
{
    /**
     * @var array<FilterInterface> $filters
     */
    protected array $filters = [];
    protected ?Order $order = null;
    protected ?int $limit = null;
    /**
     * @var array<int> $records
     */
    protected array $records = [];

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

    /**
     * Note. Result will contains only records with defined field data.
     * If your data structure is not normalized, records without sorting field will be ignored
     * @param string $fieldName
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
    public function order(string $fieldName, int $direction = Order::SORT_ASC, int $sortFlags = SORT_REGULAR): self
    {
        $this->order = new Order($fieldName, $direction, $sortFlags);
        return $this;
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
     * Order getter
     *
     * @return ?Order
     */
    public function getOrder(): ?Order
    {
        return $this->order;
    }

    /**
     * Filters getter
     * @return array<FilterInterface>
     */
    public function getFilters(): array
    {
        return $this->filters;
    }
}
