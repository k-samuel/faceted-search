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
     * @inheritDoc
     */
    public function filterResults(array $facetedData, ?array $inputRecords = null) : array
    {
        $result = [];
        $value = $this->getValue();
        $min = null;
        $max = null;

        $min = $value['min'];
        $max = $value['max'];

        // collect list for different values of one property
        if($min !== null || $max !== null)
        {
            $limitData = [];
            foreach ($facetedData as $value => $records){
                if ($min !== null && (float) $value < (float) $min) {
                    continue;
                }
                if ($max !== null && (float) $value > (float) $max) {
                    continue;
                }
                $limitData = array_merge($limitData, $records);
            }

            if (empty($limitData)) {
                return [];
            }

            if ($inputRecords === null) {
                $result = $limitData;
            } else {
                $result = array_intersect($inputRecords, $limitData);
            }
        }
        return array_values($result);
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function setValue($value) : void
    {
        if(!is_array($value) || (!isset($value['min']) && !isset($value['max']))){
            throw new \Exception('Wrong value format for RangeFilter. Expected format ["min"=>0,"max"=>100]');
        }
        $this->value = [
            'min'=>$value['min']?? null,
            'max'=>$value['max']?? null,
        ];
    }
}