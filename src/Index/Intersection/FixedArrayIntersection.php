<?php

/**
 *
 * MIT License
 *
 * Copyright (C) 2020-2023  Kirill Yegorov https://github.com/k-samuel
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

namespace KSamuel\FacetedSearch\Index\Intersection;

/**
 *  Performance patch SplFixedArray index access is faster than iteration
 */
class FixedArrayIntersection implements IntersectionInterface
{
    /**
     * Get intersection count
     * @param array<int>|\SplFixedArray<int> $a
     * @param array<int,bool> $b
     * @return int
     */
    public function getIntersectMapCount($a, array $b): int
    {
        $intersectLen = 0;

        foreach ($a as $key) {
            if (isset($b[$key])) {
                $intersectLen++;
            }
        }

        return $intersectLen;
    }

    /**
     * Check if arrays has intersection
     * @param array<int>|\SplFixedArray<int> $a
     * @param array<int,bool> $b
     * @return bool
     */
    public function hasIntersectIntMap($a, array $b): bool
    {
        $count = count($a);
        for ($i = 0; $i < $count; $i++) {
            if (isset($b[$a[$i]])) {
                return true;
            }
        }
        return false;
    }
}
