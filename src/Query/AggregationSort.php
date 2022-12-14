<?php

/**
 *
 * MIT License
 *
 * Copyright (C) 2022  Kirill Yegorov https://github.com/k-samuel
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

namespace KSamuel\FacetedSearch\Query;


class AggregationSort
{
    public const SORT_ASC = 0;
    public const SORT_DESC = 1;
    private int $direction = self::SORT_ASC;
    private int $sortFlags = SORT_REGULAR;

    /**
     * Aggregation results sort
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
    public function __construct(int $direction = self::SORT_ASC, int $sortFlags = SORT_REGULAR)
    {
        $this->direction = $direction;
        $this->sortFlags = $sortFlags;
    }

    public function getDirection(): int
    {
        return $this->direction;
    }

    public function getSortFlags(): int
    {
        return $this->sortFlags;
    }
}
