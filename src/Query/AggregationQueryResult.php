<?php

/**
 *
 * MIT License
 *
 * Copyright (C) 2020-2026  Kirill Yegorov https://github.com/k-samuel
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

use KSamuel\FacetedSearch\Query\AggregationSort;

class AggregationQueryResult
{
    /**
     * Count unique records by field value
     *
     * @var array<int|string,array<int|string,int|bool>>
     */
    private $valueCount = [];
    /**
     * Count unique records by field
     *
     * @var array<string|int,int>
     */
    private $fieldTotal = [];

    /**
     * Add result by field value
     * @param string|int $field
     * @param string|int $value
     * @param int|bool $count
     * @return void
     */
    public function setValueCount($field, $value, $count)
    {
        $this->valueCount[$field][$value] = $count;
    }

    /**
     * Set count of unique records for field
     *
     * @param int|string $field
     * @param int $count
     * @return void
     */
    public function setFieldTotal($field, int $count): void
    {
        $this->fieldTotal[$field] = $count;
    }


    /**
     * Sort aggregation result fields and values
     * @param AggregationSort $sort
     */
    public function sort(AggregationSort $sort): void
    {
        $sortFlags = $sort->getSortFlags();
        if ($sort->getDirection() === AggregationSort::SORT_ASC) {
            ksort($this->valueCount, $sortFlags);
            foreach ($this->valueCount as $k => &$v) {
                ksort($v, $sortFlags);
            }
            unset($v);
        } else {
            krsort($this->valueCount, $sortFlags);
            foreach ($this->valueCount as $k => &$v) {
                krsort($v, $sortFlags);
            }
            unset($v);
        }
    }

    /**
     * Get records count by field values
     *
     * @return array<int|string,array<int|string,int|bool>>
     */
    public function getValues(): array
    {
        return $this->valueCount;
    }

    /**
     * Get Total records count for fields
     * @return array <int|string,int>
     */
    public function getFields(): array
    {
        return $this->fieldTotal;
    }
}
