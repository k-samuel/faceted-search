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

namespace KSamuel\FacetedSearch\Index;

use KSamuel\FacetedSearch\Index\Sort;
use KSamuel\FacetedSearch\Index\Storage\ArrayStorage;
use KSamuel\FacetedSearch\Index\Storage\FixedArrayStorage;
use KSamuel\FacetedSearch\Index\Storage\StorageInterface;
use KSamuel\FacetedSearch\Index\Storage\Scanner;
use KSamuel\FacetedSearch\Index\Intersection\ArrayIntersection;
use KSamuel\FacetedSearch\Index\Intersection\FixedArrayIntersection;
use KSamuel\FacetedSearch\Index\Sort\ArrayResults;
use KSamuel\FacetedSearch\Index\Sort\FixedArrayResults;

final class Factory
{
    const ARRAY_STORAGE = ArrayStorage::class;
    const FIXED_ARRAY_STORAGE = FixedArrayStorage::class;

    public function create(string $storage): IndexInterface
    {
        /**
         * @var StorageInterface $store
         */
        $store = new $storage;

        if (!$store instanceof StorageInterface) {
            throw new \InvalidArgumentException('$storage is not instance of ' . StorageInterface::class);
        }

        if ($storage === self::FIXED_ARRAY_STORAGE) {
            $intersection = new FixedArrayIntersection;
            $resultSort = new FixedArrayResults;
        } else {
            $intersection = new ArrayIntersection;
            $resultSort = new ArrayResults;
        }

        return new Index(
            $store,
            new Sort\Filters,
            new Sort\AggregationResults,
            $resultSort,
            new Scanner,
            $intersection
        );
    }
}
