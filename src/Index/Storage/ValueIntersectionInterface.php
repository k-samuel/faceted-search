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

namespace KSamuel\FacetedSearch\Index\Storage;

use KSamuel\FacetedSearch\Index\Storage\ValueInterface;

interface ValueIntersectionInterface
{
    /**
     * Get intersection count
     * @param ValueInterface $value
     * @param array<int,bool> $recordIds
     * @return int
     */
    public function getIntersectionCount(ValueInterface $value, array $recordIds): int;

    /**
     * Check if arrays has intersection
     * @param ValueInterface $value
     * @param array<int,bool> $recordIds
     * @return bool
     */
    public function hasIntersection(ValueInterface $value, array $recordIds): bool;
}
