<?php

/**
 *
 * MIT License
 *
 * Copyright (C) 2020-2024  Kirill Yegorov https://github.com/k-samuel
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

interface FieldInterface
{
    /**
     *
     * @param string|int $fieldName
     * @param array<int|string,array<int,int>|\SplFixedArray<int>> $data
     * @return void
     */
    public function setDataLink($fieldName, array &$data): void;

    /**
     * Get list of field values
     * @return Generator<int|string>
     */
    public function values(): Generator;

    /**
     * @return ValueInterface
     */
    public function value(): ValueInterface;

    /**
     * Set value link into field container
     *
     * @param string|int $value
     * @param ValueInterface $fieldContainer
     * @return void
     */
    public function linkValue($value, ValueInterface $fieldContainer): void;

    /**
     * @return int|string
     */
    public function getName();

    /**
     * @param string|int $value
     * @return bool
     */
    public function hasValue($value): bool;
}
