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

class Scanner
{
    /**
     * Find records by filters as array map [$id1=>true, $id2=>true, ...]
     * @param StorageInterface $storage
     * @param array<\KSamuel\FacetedSearch\Filter\FilterInterface> $filters
     * @param array<int,bool> $inputRecords
     * @param array<int,bool> $excludeRecords
     * @return array<int,bool>
     */
    public function findRecordsMap(StorageInterface $storage, array $filters, array $inputRecords, array $excludeRecords): array
    {
        // if no filters passed
        if (empty($filters)) {
            return $this->findInput($storage, $inputRecords, $excludeRecords);
        }

        $field = $storage->field();

        foreach ($filters as $filter) {

            $fieldName = $filter->getFieldName();

            if (!$storage->hasField($fieldName)) {
                return [];
            }

            $storage->linkField($fieldName, $field);

            $filter->filterInput($field, $inputRecords, $excludeRecords);

            if (empty($inputRecords)) {
                return [];
            }
        }

        return $inputRecords;
    }

    /**
     * Find records by exclude filters as array map [$id1=>true, $id2=>true, ...]
     * @param StorageInterface $storage
     * @param array<int|string,\KSamuel\FacetedSearch\Filter\ExcludeFilterInterface> $filters
     * @param array<int,bool> & $excludeRecords
     * @return void
     */
    public function findExcludeRecordsMap(StorageInterface $storage, array $filters, array &$excludeRecords): void
    {
        // if no filters passed
        if (empty($filters)) {
            return;
        }

        $field = $storage->field();
        foreach ($filters as $filter) {
            $fieldName = $filter->getFieldName();
            if ($storage->hasField($fieldName)) {
                $storage->linkField($fieldName, $field);
                $filter->addExcluded($field, $excludeRecords);
            }
        }
    }

    /**
     * Find records without filters as array map [$id1=>true, $id2=>true, ...]
     * @param StorageInterface $storage
     * @param array<int,bool> $inputRecords
     * @param array<int,bool> $excludeRecords
     * @return array<int,bool>
     */
    private function findInput(StorageInterface $storage, array $inputRecords, array $excludeRecords): array
    {
        $total = $this->getAllRecordIdMap($storage);

        if (empty($inputRecords) && empty($excludeRecords)) {
            /**
             * @var array<int,bool> $total
             */
            return $total;
        }

        if (!empty($inputRecords)) {
            foreach ($total as $k => $v) {
                if (!isset($inputRecords[$k])) {
                    unset($total[$k]);
                }
            }
        }

        // remove excluded records from result
        if (!empty($excludeRecords)) {
            if (count($total) > count($excludeRecords)) {
                foreach ($excludeRecords as $key => $bool) {
                    unset($total[$key]);
                }
            } else {
                foreach ($total as $key => $bool) {
                    if (isset($excludeRecords[$key])) {
                        unset($total[$key]);
                    }
                }
            }
        }

        return $total;
    }

    /**
     * Get all records from index as map [$id1=>true,...]
     * @param StorageInterface $storage
     * @return array<int,bool>
     */
    public function getAllRecordIdMap(StorageInterface $storage): array
    {
        $result = [];

        $field = $storage->field();
        $val = $field->value();
        foreach ($storage->fieldNames() as $fieldName) {
            $storage->linkField($fieldName, $field);
            foreach ($field->values() as $value) {
                $field->linkValue($value, $val);
                foreach ($val->ids() as $id) {
                    $result[$id] = true;
                }
            }
        }
        /**
         * @var array<int,bool> $result
         */

        return $result;
    }
}
