<?php

/**
 *
 * MIT License
 *
 * Copyright (C) 2021-2023 Kirill Yegorov https://github.com/k-samuel
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

use KSamuel\FacetedSearch\Index\Storage\ArrayStorage;

/**
 * Simple faceted index
 * @package KSamuel\FacetedSearch
 */
class FixedArrayStorage extends ArrayStorage
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

        foreach ($this->indexers as $fieldName => $item) {
            $item->optimize($this->data[$fieldName]);
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
    public function convert(): void
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
        $this->convert();
    }


    /**
     * @inheritDoc
     */
    public function optimize(): void
    {
        if ($this->isCompact) {
            $this->writeMode();
        }
        parent::optimize();
        $this->convert();
    }

    /**
     * @inheritDoc
     * @throws \RuntimeException
     */
    public function deleteRecord(int $recordId): bool
    {
        if ($this->isCompact) {
            $this->writeMode();
        }
        return parent::deleteRecord($recordId);
    }

    /**
     * @inheritDoc
     * @throws \RuntimeException
     */
    public function replaceRecord(int $recordId, array $recordValues): bool
    {
        if ($this->isCompact) {
            $this->writeMode();
        }
        return parent::replaceRecord($recordId, $recordValues);
    }

    /**
     * Add record to index
     * @param int $recordId
     * @param array<int|string,array<int,mixed>> $recordValues -  ['fieldName'=>'fieldValue','fieldName2'=>['val1','val2']]
     * @return bool
     */
    public function addRecord(int $recordId, array $recordValues): bool
    {
        if ($this->isCompact) {
            $this->writeMode();
        }
        return parent::addRecord($recordId, $recordValues);
    }
}
