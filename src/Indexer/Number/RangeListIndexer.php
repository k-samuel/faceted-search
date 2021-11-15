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

class RangeListIndexer implements IndexerInterface
{
    /**
     * @var array<int>
     */
    protected $ranges;
    /**
     * CustomRangeIndexer constructor.
     * @param array<int> $ranges
     */
    public function __construct(array $ranges)
    {
        sort($ranges);
        $this->ranges = $ranges;
    }

    /**
     * @param array<int|string,array<int>> $indexContainer
     * @param int $recordId
     * @param array<int,int|float> $values
     * @return bool
     */
    public function add(&$indexContainer, int $recordId, array $values): bool
    {
        foreach ($values as $value){
            $indexContainer[$this->detectRangeKey($value)][$recordId] = true;
        }
        return true;
    }

    /**
     * Detect range position key
     * @param int|float $value
     * @return int
     */
    protected function detectRangeKey($value) : int
    {
        $lastKey = 0;
        foreach ($this->ranges as $key)
        {
            if($value >= $lastKey && $value < $key){
                return $lastKey;
            }
            $lastKey = $key;
        }
        return $lastKey;
    }
}