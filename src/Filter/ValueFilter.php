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

use KSamuel\FacetedSearch\Index\Storage\FieldInterface;

/**
 * Simple filter for faceted index. Filter item by value
 * @package KSamuel\FacetedSearch\Filter
 */
class ValueFilter extends AbstractFilter
{
    /**
     * @var array<int,int|string>
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

            /**
             * @var int|string $value
             */

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
    public function filterInput(FieldInterface $field,  array &$inputIdKeys, array $excludeRecords): void
    {
        if (empty($inputIdKeys)) {
            $inputIdKeys = $this->filterData($field, $excludeRecords);
            return;
        }

        $emptyExclude = empty($excludeRecords);

        $value = $field->value();

        // collect list for different values of one property
        foreach ($this->value as $item) {

            if (!$field->hasValue($item)) {
                continue;
            }
            $field->linkValue($item, $value);

            foreach ($value->ids() as $recId) {
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
     * @param FieldInterface $field
     * @param array<int,bool> $excludeRecords
     * @return array<int,bool> - results in keys
     */
    private function filterData(FieldInterface $field, array $excludeRecords): array
    {
        $result = [];

        $emptyExclude = empty($excludeRecords);

        $value = $field->value();
        // collect list for different values of one property
        foreach ($this->value as $item) {

            if (!$field->hasValue($item)) {
                continue;
            }

            $field->linkValue($item, $value);

            // fast fill unique records (memory allocation optimization)
            if (empty($result) && $emptyExclude) {
                $result = $value->getIdMap();
                continue;
            }

            foreach ($value->ids() as $recId) {
                if ($emptyExclude || !isset($excludeRecords[$recId])) {
                    /**
                     * @var int $recId
                     */
                    $result[$recId] = true;
                }
            }
        }
        return $result;
    }
}
