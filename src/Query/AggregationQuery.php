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
    public function getInRecords(): ?array
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
}
