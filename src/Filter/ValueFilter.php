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
class ValueFilter extends AbstractFilter implements InputFilterInterface
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
    public function filterResults(array $facetedData, ?array $inputIdKeys = null): array
    {
        $result = [];
        $hasInput = !empty($inputIdKeys);

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
                    if (!$hasInput) {
                        $result[$recId] = true;
                        continue;
                    }

                    if (isset($inputIdKeys[$recId])) {
                        $result[$recId] = true;
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
                    if (!$hasInput) {
                        $result[$recId] = true;
                        continue;
                    }

                    if (isset($inputIdKeys[$recId])) {
                        $result[$recId] = true;
                    }
                }
            }
        }
        return $result;
    }

     /**
     * @inheritDoc
     */
    public function filterInput(array $facetedData,  array & $inputIdKeys) : void
    {
        if(empty($inputIdKeys)){
            $inputIdKeys = $this->filterData($facetedData);
            return;
        }

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
                    if (isset($inputIdKeys[$recId])) {
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
                    if (isset($inputIdKeys[$recId])) {
                        $inputIdKeys[$recId] = 2;
                    }
                }
            }
        }

        foreach ($inputIdKeys as $index => &$value){
            if($value === 2){
                $value = true;
                continue;
            }
            unset($inputIdKeys[$index]);
        }
    }

    /** 
     * Filter faceted data
     * @param array<int|string,array<int>|\SplFixedArray<int>> $facetedData
     * @return array<int,bool> - results in keys
     */
    private function filterData(array $facetedData) : array
    {
        $result = [];
      
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
                    $result[$recId] = true;
                    continue;
                }
            } else {
                // Performance patch SplFixedArray index access is faster than iteration
                $count = count($facetedData[$item]);
                for ($i = 0; $i < $count; $i++) {
                    $recId = $facetedData[$item][$i];
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