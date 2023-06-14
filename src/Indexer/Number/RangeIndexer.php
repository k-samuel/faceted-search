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

namespace KSamuel\FacetedSearch\Indexer\Number;

use KSamuel\FacetedSearch\Indexer\IndexerInterface;

class RangeIndexer implements IndexerInterface
{
    /**
     * @var int
     */
    protected int $step;

    protected bool $hasUnsorted = false;
    /**
     * New values for sorting
     * @var array<int|string,array<int|string,array<int>>>
     */
    protected array $unsortedBuf = [];

    public function __construct(int $step)
    {
        if ($step <= 0) {
            throw new \Exception('Invalid step value: ' . $step);
        }
        $this->step = $step;
    }

    /**
     * @param array<int|string,array<int>> $indexContainer
     * @param int $recordId
     * @param array<int,int|float> $values
     * @return bool
     */
    public function add(&$indexContainer, int $recordId, array $values): bool
    {
        $this->hasUnsorted = true;

        foreach ($values as $value) {
            $position = (int)((float) $value / $this->step);
            $position = ($position) * $this->step;
            $indexContainer[$position][] = $recordId;
            if (!isset($this->unsortedBuf[$position][$value])) {
                $this->unsortedBuf[$position][$value] = [];
            }
            $this->unsortedBuf[$position][$value][] = $recordId;
        }
        return true;
    }
    /**
     * Prepare values for export
     *
     * @param array<int|string,array<int|string,array<int>>> &$indexContainer
     * @return void
     */
    public function optimize(array &$indexContainer): void
    {
        if (!$this->hasUnsorted) {
            return;
        }

        foreach ($this->unsortedBuf as $position => &$values) {
            ksort($values);
            foreach ($values as $value => $ids) {
                foreach ($ids as $id) {
                    $this->addSorterId($indexContainer[$position], $id);
                }
                $indexContainer[$position] = array_values($indexContainer[$position]);
            }
        }
        unset($values);
        $this->unsortedBuf = [];
        $this->hasUnsorted = false;
    }
    /**
     * Add sorted element
     *
     * @param array<int,int> & $valueContainer
     * @param int $id
     * @return void
     */
    private function addSorterId(array &$valueContainer, int $id): void
    {
        foreach ($valueContainer as $index => $recordId) {
            if ($recordId === $id) {
                unset($valueContainer[$index]);
                $valueContainer[] = $id;
                return;
            }
        }
    }
}
