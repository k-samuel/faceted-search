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

namespace KSamuel\FacetedSearch\Index\Sort;

use KSamuel\FacetedSearch\Index\Storage\StorageInterface;
use KSamuel\FacetedSearch\Query\Order;

class FixedArrayResults implements QueryResultsInterface
{
    /**
     * Sort results by field value
     * @param StorageInterface $storage
     * @param array<int,bool> $resultsMap
     * @param Order $order
     * @return array<int>
     */
    public function sort(StorageInterface $storage, array $resultsMap, Order $order): array
    {
        $field = $order->getField();

        if (!$storage->hasField($field)) {
            return [];
        }

        $fieldData = $storage->getFieldData($field);
        $values = array_keys($fieldData);

        if ($order->getDirection() === Order::SORT_ASC) {
            sort($values, $order->getSortFlags());
        } else {
            rsort($values, $order->getSortFlags());
        }

        $sorted = [];
        foreach ($values as $value) {
            $records = $fieldData[$value];
            // inline intersection - intersectIntMap
            // Performance patch SplFixedArray index access is faster than iteration
            $count = count($records);
            if ($order->getDirection() === Order::SORT_ASC) {
                for ($i = 0; $i < $count; $i++) {
                    /**
                     * @var int $key
                     */
                    $key = $records[$i];
                    if (isset($resultsMap[$key])) {
                        $sorted[] = $key;
                        // already sorted
                        unset($resultsMap[$key]);
                    }
                }
            } else {
                $last = $count - 1;
                for ($i = $last; $i >= 0; $i--) {
                    /**
                     * @var int $key
                     */
                    $key = $records[$i];
                    if (isset($resultsMap[$key])) {
                        $sorted[] = $key;
                        // already sorted
                        unset($resultsMap[$key]);
                    }
                }
            }
        }
        return $sorted;
    }
}
