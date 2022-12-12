<?php

/**
 *
 * MIT License
 *
 * Copyright (C) 2021-2022 Kirill Yegorov https://github.com/k-samuel
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

/**
 * Simple faceted index
 * @package KSamuel\FacetedSearch
 */
class FixedArrayIndex extends ArrayIndex
{
    /**
     * @var bool
     */
    protected bool $isCompact = false;

    /**
     * Get index data. Can be used for storing it to DB
     * @return array<int|string,array<int|string,array<int>>>
     */
    public function export(): array
    {
        if ($this->isCompact) {
            $this->writeMode();
        }
        /**
         * @var array<int|string,array<int|string,array<int>>>
         */
        return $this->data;
    }

    /**
     * Enable write mode, don't forget to commit changes
     */
    public function writeMode(): void
    {
        foreach ($this->data as &$value) {
            /**
             * @var \SplFixedArray<int> $recordList
             */
            foreach ($value as &$recordList) {
                if ($recordList instanceof \SplFixedArray) {
                    $recordList = $recordList->toArray();
                }
            }
            unset($recordList);
        }
        unset($value);
        $this->isCompact = false;
    }

    /**
     * Apply index updates (convert into \SplFixedArray)
     */
    public function commitChanges(): void
    {
        foreach ($this->data as &$value) {
            /**
             * @var array<int>|\SplFixedArray<int> $recordList
             */
            foreach ($value as &$recordList) {
                if (is_array($recordList)) {
                    $recordList = \SplFixedArray::fromArray($recordList);
                }
            }
            unset($recordList);
        }
        unset($value);

        $this->isCompact = true;
    }

    /**
     * @inheritDoc
     */
    public function setData(array $data): void
    {
        $this->isCompact = false;
        $this->data = $data;
        $this->resetLocalCache();
        $this->commitChanges();
    }


    /**
     * @inheritDoc
     * Performance patch SplFixedArray index access is faster than iteration
     */
    protected function getIntersectMapCount($a, array $b): int
    {
        $intersectLen = 0;
        $count = count($a);
        for ($i = 0; $i < $count; $i++) {
            if (isset($b[$a[$i]])) {
                $intersectLen++;
            }
        }
        return $intersectLen;
    }

    /**
     * @inheritDoc
     * Performance patch SplFixedArray index access is faster than iteration
     */
    protected function hasIntersectIntMap($a, array $b): bool
    {
        $intersectLen = 0;
        $count = count($a);
        for ($i = 0; $i < $count; $i++) {
            if (isset($b[$a[$i]])) {
                return true;
            }
        }
        return false;
    }
}
