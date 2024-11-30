<?php

/**
 *
 * MIT License
 *
 * Copyright (C) 2020-2024  Kirill Yegorov https://github.com/k-samuel
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


final class Factory
{
    const ARRAY_STORAGE = ArrayStorage\Storage::class;
    const FIXED_ARRAY_STORAGE = FixedArrayStorage\Storage::class;


    public function create(string $storage): IndexInterface
    {
        /**
         * @var StorageInterface $store
         */
        $store = new $storage;

        if (!$store instanceof StorageInterface) {
            throw new \InvalidArgumentException('$storage is not instance of ' . StorageInterface::class);
        }

        switch ($storage) {
            case self::ARRAY_STORAGE:
                $intersection = new ArrayStorage\ValueIntersection;
                $resultSort = new ArrayStorage\Sort\Results;
                break;
            case self::FIXED_ARRAY_STORAGE:
                $intersection = new FixedArrayStorage\ValueIntersection;
                $resultSort = new FixedArrayStorage\Sort\Results;
                break;
            default:
                throw new \InvalidArgumentException('Undefined storage class ' . $storage);
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
