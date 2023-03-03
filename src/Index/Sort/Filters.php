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

namespace KSamuel\FacetedSearch\Index\Sort;

use KSamuel\FacetedSearch\Filter\FilterInterface;
use KSamuel\FacetedSearch\Filter\ValueFilter;
use KSamuel\FacetedSearch\Index\Storage\StorageInterface;

class Filters
{
    /**
     * Sort filters by minimum values count
     * Used for aggregation optimization
     *
     * @param StorageInterface $storage
     * @param array<FilterInterface> $filters
     * @return array<FilterInterface>
     */
    public function byCount(StorageInterface $storage, array $filters): array
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

            if (!$storage->hasField($fieldName)) {
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
                $cnt = $storage->getRecordsCount($fieldName, $value);
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
}
