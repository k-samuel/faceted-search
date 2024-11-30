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

namespace KSamuel\FacetedSearch\Index\Storage\FixedArrayStorage;

use KSamuel\FacetedSearch\Index\Storage\ValueInterface;

use InvalidArgumentException;
use SplFixedArray;

class Value implements ValueInterface
{

    /**
     * @var SplFixedArray<int>
     */
    private SplFixedArray $data;

    /**
     * @param SplFixedArray<int> $data
     * @return void
     */
    public function setDataLink(&$data): void
    {
        if (!$data instanceof SplFixedArray) {
            throw new InvalidArgumentException('Trying to read FixedArrayStorage which is in write mode. Please call $storage->optimize() before reading.');
        }
        $this->data = $data;
    }

    /**
     * @return SplFixedArray<int>
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @inheritDoc
     */
    public function ids(): array
    {
        /**
         * @phpstan-ignore-next-line
         */
        return $this->data->toArray();
    }

    /**
     * @inheritDoc
     */
    public function getIdMap(): array
    {
        $result = [];
        $count = count($this->data);
        for ($i = 0; $i < $count; $i++) {
            $result[$this->data[$i]] = true;
        }
        /**
         * @var array<int,bool> $result
         */
        return $result;
    }

    public function isEmpty(): bool
    {
        return empty($this->data);
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return $this->data->count();
    }
}
