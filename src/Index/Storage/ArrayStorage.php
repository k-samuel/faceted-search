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

use KSamuel\FacetedSearch\Indexer\IndexerInterface;
use Generator;

/**
 * Simple faceted index
 * @package KSamuel\FacetedSearch
 */
class ArrayStorage implements StorageInterface
{
    /**
     * Index data
     * @var array<int|string,array<int|string,array<int>>>
     */
    protected array $data = [];
    /**
     * @var array<int|string,IndexerInterface>
     */
    protected array $indexers = [];

    /**
     * Add record to index
     * @param int $recordId
     * @param array<int|string,array<int,mixed>> $recordValues -  ['fieldName'=>'fieldValue','fieldName2'=>['val1','val2']]
     * @return bool
     */
    public function addRecord(int $recordId, array $recordValues): bool
    {
        foreach ($recordValues as $fieldName => $values) {
            if (!is_array($values)) {
                $values = [$values];
            }

            $values = array_unique($values);

            if (isset($this->indexers[$fieldName])) {
                if (!isset($this->data[$fieldName])) {
                    $this->data[$fieldName] = [];
                }
                if (!$this->indexers[$fieldName]->add($this->data[$fieldName], $recordId, $values)) {
                    return false;
                }
            } else {
                foreach ($values as $value) {
                    if (is_bool($value)) {
                        $value = (int)$value;
                    }
                    if (is_float($value)) {
                        $value = (string)$value;
                    }
                    $this->data[$fieldName][$value][] = $recordId;
                }
            }
        }
        return true;
    }

    /**
     * Get facet data.
     * @return array<int|string,array<int|string,array<int>|\SplFixedArray<int>>>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Get facet data.
     * @return array<int|string,array<int|string,array<int>>>
     */
    public function export(): array
    {
        foreach ($this->indexers as $fieldName => $item) {
            $item->optimize($this->data[$fieldName]);
        }

        return $this->data;
    }

    /**
     * Set index data. Can be used for restoring from DB
     * @param array<int|string,array<int|string,array<int>>> $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * Get field data section from index
     * @param string $fieldName
     * @return array<int|string,array<int>|\SplFixedArray<int>>
     */
    public function getFieldData(string $fieldName): array
    {
        return $this->data[$fieldName] ?? [];
    }

    /**
     * Add specialized indexer for field
     * @param int|string $fieldName
     * @param IndexerInterface $indexer
     */
    public function addIndexer($fieldName, IndexerInterface $indexer): void
    {
        $this->indexers[$fieldName] = $indexer;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return int
     */
    public function getRecordsCount(string $field, $value): int
    {
        if (!isset($this->data[$field][$value])) {
            return 0;
        }
        return count($this->data[$field][$value]);
    }

    /**
     * Check if field exists
     * @param string $fieldName
     * @return bool
     */
    public function hasField(string $fieldName): bool
    {
        return (isset($this->data[$fieldName]) && !empty($this->data[$fieldName]));
    }

    /**
     * @return array<int|string,array<string|int,true>>
     */
    protected function getValues(): array
    {
        $result = [];
        foreach ($this->data as $filterName => $filterValues) {
            foreach ($filterValues as $key => $info) {
                $result[$filterName][$key] = true;
            }
        }
        return $result;
    }
    /**
     * @return array<int|string,array<string|int,int>>
     */
    protected function getValuesCount(): array
    {
        $result = [];
        foreach ($this->data as $filterName => $filterValues) {
            foreach ($filterValues as $key => $list) {
                $result[$filterName][$key] = count($list);
            }
        }
        return $result;
    }

    public function optimize(): void
    {
        foreach ($this->indexers as $fieldName => $item) {
            $item->optimize($this->data[$fieldName]);
        }

        foreach ($this->data as $fieldName => &$valueList) {

            $valueCounts = [];
            foreach ($valueList as $value => &$list) {
                $valueCounts[$value] = count($list);

                // sort records by id ASC exclude range indexers data sorted by value
                if (!isset($this->indexers[$fieldName])) {
                    sort($list);
                }
            }
            // sort values by records count
            asort($valueCounts);
            $oldList = $valueList;
            $valueList = [];
            foreach ($valueCounts as $value => $count) {
                $valueList[$value] = $oldList[$value];
            }
        }
    }

    /**
     * Delete record from index
     * @param int $recordId
     * @return bool - success flag
     */
    public function deleteRecord(int $recordId): bool
    {
        foreach ($this->data as $fieldName => &$valueList) {
            foreach ($valueList as $fieldValue => &$list) {
                $hasDeletion = false;
                foreach ($list as $index => $id) {
                    if ($id === $recordId) {
                        unset($list[$index]);
                        $hasDeletion = true;
                    }
                }
                // reset array numeration
                if ($hasDeletion) {
                    if (empty($list)) {
                        // clean empty value
                        unset($valueList[$fieldValue]);
                    } else {
                        $list = array_values($list);
                    }
                }
            }
            if (empty($valueList)) {
                // clean empty field
                unset($this->data[$fieldName]);
            }
        }
        return true;
    }

    /**
     * Update record data
     * @param int $recordId
     * @param array<int|string,array<int,mixed>> $recordValues -  ['fieldName'=>'fieldValue','fieldName2'=>['val1','val2']]
     * @return bool - success flag
     */
    public function replaceRecord(int $recordId, array $recordValues): bool
    {
        if (!$this->deleteRecord($recordId)) {
            return false;
        }

        return $this->addRecord($recordId, $recordValues);
    }

    /**
     * List data
     * @return Generator
     */
    public function scan(): Generator
    {
        foreach ($this->data as $k => $v) {
            yield $k => $v;
        }
    }
}
