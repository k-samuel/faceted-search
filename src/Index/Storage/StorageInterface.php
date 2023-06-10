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

use Generator;
use KSamuel\FacetedSearch\Indexer\IndexerInterface;

/**
 * Simple faceted index
 * @package KSamuel\FacetedSearch
 */
interface StorageInterface
{
    /**
     * Add record to index
     * @param int $recordId
     * @param array<int|string,array<int,mixed>> $recordValues -  ['fieldName'=>'fieldValue','fieldName2'=>['val1','val2']]
     * @return bool
     */
    public function addRecord(int $recordId, array $recordValues): bool;

    /**
     * Get field data section from index
     * @param string $fieldName
     * @return array<int|string,array<int>>
     */
    public function getFieldData(string $fieldName): array;

    /**
     * Check if field exists
     * @param string $fieldName
     * @return bool
     */
    public function hasField(string $fieldName): bool;

    /**
     * Get facet data.
     * @return array<int|string,array<int|string,array<int>|\SplFixedArray<int>>>
     */
    public function getData(): array;

    /**
     * Export facet index data.
     * @return array<int|string,array<int|string,array<int>>>
     */
    public function export(): array;

    /**
     * Load saved data
     * @param array<mixed> $data
     * @return void
     */
    public function setData(array $data);

    /**
     * Optimize index structure
     * @return void
     */
    public function optimize(): void;

    /**
     * Delete record from index
     * @param int $recordId
     * @return bool - success flag
     */
    public function deleteRecord(int $recordId): bool;

    /**
     * Update record data
     * @param int $recordId
     * @param array<int|string,array<int,mixed>> $recordValues -  ['fieldName'=>'fieldValue','fieldName2'=>['val1','val2']]
     * @return bool - success flag
     */
    public function replaceRecord(int $recordId, array $recordValues): bool;

    /**
     * Add specialized indexer for field
     * @param int|string $fieldName
     * @param IndexerInterface $indexer
     */
    public function addIndexer($fieldName, IndexerInterface $indexer): void;

    /**
     * @param string $field
     * @param mixed $value
     * @return int
     */
    public function getRecordsCount(string $field, $value): int;

    /**
     * List data
     * @return Generator
     */
    public function scan(): Generator;
}
