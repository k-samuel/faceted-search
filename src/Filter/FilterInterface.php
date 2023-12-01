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

namespace KSamuel\FacetedSearch\Filter;

interface FilterInterface
{
    /**
     * Get field name
     * @return string
     */
    public function getFieldName(): string;

    /**
     * Filter faceted data
     * @param array<int|string,array<int>|\SplFixedArray<int>> $facetedData
     * @param array<int,bool|int> & $inputIdKeys - RecordId passed into keys of an array (performance issue)
     * @param array<int,bool> $excludeRecords - RecordId passed into keys of an array (performance issue)
     * @return void
     */
    public function filterInput(array $facetedData,  array &$inputIdKeys, array $excludeRecords): void;

    /**
     *  Get self-filtering flag
     *
     * @return bool
     */
    public function hasSelfFiltering(): bool;
}
