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


abstract class AbstractFilter implements FilterInterface
{
    /**
     * @var string
     */
    protected string $fieldName;
    /**
     * @var mixed
     */
    protected $value;

    /**
     * Self filtering is disabled by default
     * @var boolean
     */
    protected bool $selfFiltering = false;

    /**
     * AbstractFilter constructor.
     * @param string $fieldName
     * @param mixed $value
     */
    public function __construct(string $fieldName, $value = null)
    {
        $this->fieldName = $fieldName;
        if ($value !== null) {
            $this->setValue($value);
        }
    }

    /**
     * @inheritDoc
     */
    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    /**
     * Set filter value
     * @param mixed $value
     * @return void
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }

    /**
     * Get filter value
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    abstract public function filterInput(array $facetedData,  array &$inputIdKeys, array $excludeRecords): void;

    /**
     * Enable/Disable self-filtering for current filter, disabled by default.
     * Used in AggregationQuery
     * @param bool $enabled
     * @return self 
     */
    public function selfFiltering(bool $enabled): self
    {
        $this->selfFiltering = $enabled;
        return $this;
    }

    /**
     * Get self-filtering flag
     * @return boolean
     */
    public function hasSelfFiltering(): bool
    {
        return $this->selfFiltering;
    }
}
