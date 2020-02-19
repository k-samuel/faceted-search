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

class ValueFilter extends AbstractFilter
{
    /**
     * @param array<array> $facetedData
     * @param array<int>|null $inputRecords
     * @return array<int>
     */
    public function filterResults(array $facetedData, ?array $inputRecords = null) : array
    {
        $result = $inputRecords;

        $value = $this->getValue();
        if(!is_array($value)){
            if(is_bool($value)){
                $value = (int) $value;
            }
            $value = [$value];
        }

        $filterResults = [];

        // collect list for different values of one property
        foreach ($value as $item) {
            if (isset($facetedData[$item])) {
                $filterResults = array_merge($filterResults, $facetedData[$item]);
            }
        }

        if (empty($filterResults)) {
            return [];
        }

        if ($result === null) {
            $result = $filterResults;
        } else {
            // find intersect of start records and faceted results
            $result = array_intersect($result, $filterResults);
        }
        return array_values($result);
    }
}