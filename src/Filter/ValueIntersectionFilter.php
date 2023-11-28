<?php

/**
 *
 * MIT License
 *
 * Copyright (C) 2023  Kirill Yegorov https://github.com/k-samuel
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
class ValueIntersectionFilter extends ValueFilter
{
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
        $result = [];
        $isFirst = true;

        // collect list for different values of one property
        foreach ($this->value as $item) {

            if (!isset($facetedData[$item])) {
                $inputIdKeys = [];
                return;
            }

            if ($isFirst) {
                if (is_array($facetedData[$item])) {
                    $result = array_fill_keys($facetedData[$item], true);
                } else {
                    // splFixedArray
                    $result = array_fill_keys($facetedData[$item]->toArray(), true);
                }
                $isFirst = false;
            }


            $tmp = [];
            foreach ($facetedData[$item] as $recId) {
                /**
                 * @var int $recId
                 */
                if (isset($inputIdKeys[$recId]) && isset($result[$recId]) && ($emptyExclude || !isset($excludeRecords[$recId]))) {
                    $tmp[$recId] = true;
                }
            }
            $result = $tmp;
        }
        $inputIdKeys = $result;
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
        $isFirst = true;

        // collect list for different values of one property
        foreach ($this->value as $item) {

            if (!isset($facetedData[$item])) {
                return [];
            }

            // fast fill unique records (memory allocation optimization)
            if ($isFirst && empty($result) && $emptyExclude) {
                if (is_array($facetedData[$item])) {
                    $result = array_fill_keys($facetedData[$item], true);
                } else {
                    // splFixedArray
                    /**
                     * @var array<int,bool> $result
                     */
                    $result = array_fill_keys($facetedData[$item]->toArray(), true);
                }
                $isFirst = false;
                continue;
            }

            $tmp = [];
            foreach ($facetedData[$item] as $recId) {
                if (isset($result[$recId]) && ($emptyExclude || !isset($excludeRecords[$recId]))) {
                    /**
                     * @var int $recId
                     */
                    $tmp[$recId] = true;
                }
            }
            $result = $tmp;
        }
        return $result;
    }
}
