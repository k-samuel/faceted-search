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

namespace KSamuel\FacetedSearch\Filter;

/**
 * Simple filter for faceted index. Filter item by value
 * @package KSamuel\FacetedSearch\Filter
 */
class ValueFilter extends AbstractFilter
{
    /**
     * @var array<int,mixed>
     */
    protected $value;

    /**
     * Set filter value
     * @param mixed $value
     * @return void
     */
    public function setValue($value): void
    {
        if (!is_array($value)) {
            if (is_bool($value)) {
                $value = (int)$value;
            }
            if (is_float($value)) {
                $value = (string)$value;
            }
            $this->value = [$value];
            return;
        }

        foreach ($value as &$item) {
            if (is_bool($item)) {
                $item = (int)$item;
            }
            if (is_float($item)) {
                $item = (string)$item;
            }
        }
        unset($item);

        $this->value = $value;
    }

    /**
     * @inheritDoc
     */
    public function filterInput(array $facetedData,  array &$inputIdKeys, array $excludeRecords): void
    {
        if (empty($inputIdKeys)) {
            $inputIdKeys = $this->filterData($facetedData, $excludeRecords);
            return;
        }

        $emptyExclude = empty($excludeRecords);


        // collect list for different values of one property
        foreach ($this->value as $item) {

            if (!isset($facetedData[$item])) {
                continue;
            }

            if (is_array($facetedData[$item])) {
                foreach ($facetedData[$item] as $recId) {
                    /**
                     * @var int $recId
                     */
                    if (isset($inputIdKeys[$recId]) && ($emptyExclude || !isset($excludeRecords[$recId]))) {
                        /*
                         * Memory optimization.
                         * Flag matching entries with value "2" instead of allocating an additional results array.
                         */
                        $inputIdKeys[$recId] = 2;
                    }
                }
            } else {
                // Performance patch SplFixedArray index access is faster than iteration
                $count = count($facetedData[$item]);
                for ($i = 0; $i < $count; $i++) {
                    $recId = $facetedData[$item][$i];
                    /**
                     * @var int $recId
                     */
                    if (isset($inputIdKeys[$recId]) && ($emptyExclude || !isset($excludeRecords[$recId]))) {
                        /*
                         Memory optimization.
                         Flag matching entries with value "2" instead of allocating an additional results array.
                         */
                        $inputIdKeys[$recId] = 2;
                    }
                }
            }
        }

        // Remove filtered records, reset matching flag
        foreach ($inputIdKeys as $index => &$value) {
            if ($value === 2) {
                $value = true;
                continue;
            }
            unset($inputIdKeys[$index]);
        }
    }

    /** 
     * Filter faceted data
     * @param array<int|string,array<int>|\SplFixedArray<int>> $facetedData
     * @param array<int,bool> $excludeRecords
     * @return array<int,bool> - results in keys
     */
    private function filterData(array $facetedData, array $excludeRecords): array
    {
        $result = [];

        $emptyExclude = empty($excludeRecords);

        // collect list for different values of one property
        foreach ($this->value as $item) {

            if (!isset($facetedData[$item])) {
                continue;
            }

            if (is_array($facetedData[$item])) {

                // fast fill unique records (memory allocation optimization)
                if (empty($result) && $emptyExclude) {
                    $result = array_fill_keys($facetedData[$item], true);
                    continue;
                }

                foreach ($facetedData[$item] as $recId) {
                    if ($emptyExclude || !isset($excludeRecords[$recId])) {
                        /**
                         * @var int $recId
                         */
                        $result[$recId] = true;
                    }
                }
            } else {
                // fast fill unique records (memory allocation optimization)
                if (empty($result) && $emptyExclude) {
                    /**
                     * @var array<int,bool> $result
                     */
                    $result = array_fill_keys($facetedData[$item]->toArray(), true);
                    continue;
                }
                // Performance patch SplFixedArray index access is faster than iteration
                $count = count($facetedData[$item]);
                for ($i = 0; $i < $count; $i++) {
                    $recId = $facetedData[$item][$i];
                    if ($emptyExclude || !isset($excludeRecords[$recId])) {
                        /**
                         * @var int $recId
                         */
                        $result[$recId] = true;
                    }
                }
            }
        }
        return $result;
    }
}
