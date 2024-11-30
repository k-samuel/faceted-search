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
 * Range filter for faceted index. Filter item by range (min,max)
 * @package KSamuel\FacetedSearch\Filter
 */
class ExcludeRangeFilter extends RangeFilter implements ExcludeFilterInterface
{
    /**
     * @inheritDoc
     */
    public function addExcluded(FieldInterface $field,  array &$excludeRecords): void
    {
        /**
         * @var array{min:int|float|null,max:int|float|null} $value
         */
        $value = $this->getValue();

        $min = $value['min'] ?? null;
        $max = $value['max'] ?? null;

        if ($min === null && $max === null) {
            return;
        }

        $valueContainer = $field->value();

        foreach ($field->values() as $value) {

            if ($min !== null && (float)$value < (float)$min) {
                continue;
            }
            if ($max !== null && (float)$value > (float)$max) {
                continue;
            }

            $field->linkValue($value, $valueContainer);
            foreach ($valueContainer->ids() as $recordId) {
                $excludeRecords[$recordId] = true;
            }
        }
    }
}
