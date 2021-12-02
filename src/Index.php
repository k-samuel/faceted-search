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

namespace KSamuel\FacetedSearch;

use KSamuel\FacetedSearch\Indexer\IndexerInterface;

/**
 * Simple faceted index
 * @package KSamuel\FacetedSearch
 */
class Index
{
    /**
     * Index data
     * @var array<int|string,array<int|string,array<int>>>
     */
    protected $data = [];
    /**
     * @var array<IndexerInterface>
     */
    protected $indexers = [];
    /**
     * @var null|array<int,bool>
     */
    private $idMapCache = null;

    /**
     * Add record to index
     * @param int $recordId
     * @param array<int|string,array<int,mixed>> $recordValues -  ['fieldName'=>'fieldValue','fieldName2'=>['val1','val2']]
     * @return bool
     */
    public function addRecord(int $recordId, array $recordValues): bool
    {
        $this->resetLocalCache();
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
                    $this->data[$fieldName][$value][$recordId] = true;
                }
            }
        }
        return true;
    }

    private function resetLocalCache(): void
    {
        $this->idMapCache = null;
    }

    /**
     * Get index data. Can be used for storing it to DB
     * @return array<int|string,array<int|string,array<int>>>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Set index data. Can be used for restoring from DB
     * @param array<int|string,array<int|string,array<int>>> $data
     */
    public function setData(array $data): void
    {
        $this->resetLocalCache();
        $this->data = $data;
    }

    /**
     * Get field data section from index
     * @param string $fieldName
     * @return array<int|string,array<int>>
     */
    public function getFieldData(string $fieldName): array
    {
        if (isset($this->data[$fieldName])) {
            return $this->data[$fieldName];
        }

        return [];
    }

    /**
     * Get all records from index
     * @return array<int>
     */
    public function getAllRecordId(): array
    {
        return array_keys($this->getAllREcordIdMap());
    }

    /**
     * Get all records from index as map [$id1=>true,...]
     * @return array<int,bool>
     */
    public function getAllRecordIdMap(): array
    {
        if ($this->idMapCache !== null) {
            return $this->idMapCache;
        }

        $result = [];
        foreach ($this->data as $values) {
            foreach ($values as $list) {
                foreach ($list as $k => $v) {
                    $result[$k] = true;
                }
            }
        }
        /**
         * @var array<int,bool> $result
         */

        $this->idMapCache = $result;
        return $result;
    }

    /**
     * Add specialized indexer for field
     * @param string $fieldName
     * @param IndexerInterface $indexer
     */
    public function addIndexer(string $fieldName, IndexerInterface $indexer): void
    {
        $this->indexers[$fieldName] = $indexer;
    }
}