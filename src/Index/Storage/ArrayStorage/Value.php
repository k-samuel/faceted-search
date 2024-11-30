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

namespace KSamuel\FacetedSearch\Index\Storage\ArrayStorage;

use KSamuel\FacetedSearch\Index\Storage\ValueInterface;

use \Generator;

class Value implements ValueInterface
{

    /**
     * Record id as value
     * @var array<int,int>
     */
    private array $data;

    /**
     * @param array<int,int> $data
     * @return void
     */
    public function setDataLink(&$data): void
    {
        $this->data = $data;
    }

    /**
     * @return array<int,int>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @inheritDoc
     */
    public function ids(): array
    {
        return $this->data;
    }

    /**
     * @inheritDoc
     */
    public function getIdMap(): array
    {
        return array_fill_keys($this->data, true);
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
        return count($this->data);
    }
}
