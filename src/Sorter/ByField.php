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

namespace KSamuel\FacetedSearch\Sorter;

use KSamuel\FacetedSearch\Index;

class ByField
{
    public const SORT_ASC = 0;
    public const SORT_DESC = 1;

    /**
     * @var Index
     */
    private $index;

    public function __construct(Index $index)
    {
        $this->index = $index;
    }

    /**
     * Note. Result will contains only records with defined field data.
     * If your data structure is not normalized, records without sorting field will be ignored
     * @param int[] $results
     * @param string $field
     * @param int $direction
     * @param int $sortFlags
     *  Sorting type flags:
     *     SORT_REGULAR - compare items normally; the details are described in the comparison operators section
     *     SORT_NUMERIC - compare items numerically
     *     SORT_STRING - compare items as strings
     *     SORT_LOCALE_STRING - compare items as strings, based on the current locale. It uses the locale, which can be changed using setlocale()
     *     SORT_NATURAL - compare items as strings using "natural ordering" like natsort()
     *     SORT_FLAG_CASE - can be combined (bitwise OR) with SORT_STRING or SORT_NATURAL to sort strings case-insensitively
     *
     * @return int[]
     */
    public function sort(
        array $results,
        string $field,
        int $direction = self::SORT_ASC,
        int $sortFlags = SORT_REGULAR
    ): array {

        $data = $this->index->getFieldData($field);
        if ($direction === self::SORT_ASC) {
            ksort($data, $sortFlags);
        } else {
            krsort($data, $sortFlags);
        }

        $results = array_flip($results);

        $sorted = [];
        foreach ($data as $records) {
            $ids = $this->intersectIntMap($records, $results);

            if (empty($ids)) {
                continue;
            }
            foreach ($ids as $id) {
                $sorted[] = $id;
                // already sorted
                unset($results[$id]);
            }
        }
        return $sorted;
    }

    /**
     * @param array<int,int> $a
     * @param array<int,bool|int> $b
     * @return array<int,int>
     */
    private function intersectIntMap(array $a, array $b): array
    {
        $result = [];
        foreach ($a as $key) {
            if (isset($b[$key])) {
                $result[] = $key;
            }
        }
        return $result;
    }
}