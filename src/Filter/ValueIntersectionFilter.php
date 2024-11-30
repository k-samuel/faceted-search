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

use KSamuel\FacetedSearch\Index\Storage\FieldInterface;

/**
 * Simple filter for faceted index. Filter item by value
 * @package KSamuel\FacetedSearch\Filter
 */
class ValueIntersectionFilter extends ValueFilter
{
    /**
     * @inheritDoc
     */
    public function filterInput(FieldInterface $field,  array &$inputIdKeys, array $excludeRecords): void
    {
        if (empty($inputIdKeys)) {
            $inputIdKeys = $this->filterData($field, $excludeRecords);
            return;
        }

        $emptyExclude = empty($excludeRecords);
        $result = [];
        $isFirst = true;

        $value = $field->value();

        // collect list for different values of one property
        foreach ($this->value as $item) {

            if (!$field->hasValue($item)) {
                $inputIdKeys = [];
                return;
            }

            $field->linkValue($item, $value);

            // memory allocation optimization
            if ($isFirst) {
                $result = $value->getIdMap();
                $isFirst = false;
                // The execution should not go to the next iteration, it is just a variable prefill
            }

            // find intersection
            $tmp = [];
            foreach ($value->ids() as $recId) {
                if (isset($inputIdKeys[$recId]) && isset($result[$recId]) && ($emptyExclude || !isset($excludeRecords[$recId]))) {
                    $tmp[$recId] = true;
                }
            }
            // save intersected list to result
            $result = $tmp;
        }
        // update input variable passed by reference
        $inputIdKeys = $result;
    }

    /** 
     * Filter faceted data
     * @param FieldInterface $field
     * @param array<int,bool> $excludeRecords
     * @return array<int,bool> - results in keys
     */
    private function filterData(FieldInterface $field, array $excludeRecords): array
    {
        $result = [];

        $emptyExclude = empty($excludeRecords);
        $isFirst = true;

        $value = $field->value();

        // collect list for different values of one property
        foreach ($this->value as $item) {

            if (!$field->hasValue($item)) {
                return [];
            }

            $field->linkValue($item, $value);

            // fast fill unique records (memory allocation optimization)
            if ($isFirst && empty($result) && $emptyExclude) {
                $result = $value->getIdMap();
                $isFirst = false;
                continue;
            }

            // find intersection
            $tmp = [];
            foreach ($value->ids() as $recId) {
                if (isset($result[$recId]) && ($emptyExclude || !isset($excludeRecords[$recId]))) {
                    $tmp[$recId] = true;
                }
            }
            // save intersected list to result
            $result = $tmp;
        }
        return $result;
    }
}
