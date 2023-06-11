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
 * Range filter for faceted index. Filter item by range (min,max)
 * @package KSamuel\FacetedSearch\Filter
 */
class RangeFilter extends AbstractFilter
{
    /**
     * @param mixed|array{min:int|float,max:int|float} $value
     * @throws \InvalidArgumentException
     */
    public function setValue($value): void
    {
        if (!is_array($value) || (!isset($value['min']) && !isset($value['max']))) {
            throw new \InvalidArgumentException('Wrong value format for RangeFilter. Expected format ["min"=>0,"max"=>100]');
        }
        $this->value = [
            'min' => $value['min'] ?? null,
            'max' => $value['max'] ?? null,
        ];
    }

    /**
     * @inheritDoc
     */
    public function filterInput(array $facetedData,  array &$inputIdKeys, array $excludeRecords): void
    {
        /**
         * @var array{min:int|float|null,max:int|float|null} $value
         */
        $value = $this->getValue();

        $min = $value['min'] ?? null;
        $max = $value['max'] ?? null;

        if ($min === null && $max === null) {
            $inputIdKeys = [];
            return;
        }

        // collect list for different values of one property
        $limit = [];
        foreach ($facetedData as $value => $records) {
            if ($min !== null && (float)$value < (float)$min) {
                continue;
            }
            if ($max !== null && (float)$value > (float)$max) {
                continue;
            }
            if (empty($limit) && empty($excludeRecords)) {
                /**
                 * @var array<int>|\SplFixedArray<int> $records
                 */
                if ($records instanceof \SplFixedArray) {
                    $limit = $records->toArray();
                } else {
                    $limit = $records;
                }
            } else {
                // array sum (faster than array_merge here)
                foreach ($records as $item) {
                    if (!isset($excludeRecords[$item])) {
                        $limit[] = $item;
                    }
                }
            }
        }

        if (empty($limit)) {
            $inputIdKeys = [];
            return;
        }

        if (empty($inputIdKeys)) {
            foreach ($limit as $v) {
                $inputIdKeys[$v] = true;
            }
            return;
        }

        // Solution without allocating memory to a new index map.
        // Reuse of input data. Set mark "2" for the data that needs to be in result.
        foreach ($limit as $index) {
            if (isset($inputIdKeys[$index])) {
                $inputIdKeys[$index] = 2;
            }
        }
        // Clear unmarked data
        foreach ($inputIdKeys as $index => &$value) {
            if ($value === 2) {
                $value = true;
                continue;
            }
            unset($inputIdKeys[$index]);
        }
    }
}
