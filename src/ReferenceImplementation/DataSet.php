<?php

/*
 * The MIT License
 *
 * Copyright 2021 zozlak.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace rdfInterface\ReferenceImplementation;

use Generator;
use Iterator;
use OutOfBoundsException;
use rdfInterface\BlankNode as iBlankNode;
use rdfInterface\Quad as iQuad;
use rdfInterface\QuadTemplate as iQuadTemplate;
use rdfInterface\QuadIterator as iQuadIterator;
use rdfInterface\Dataset as iDataset;
use rdfInterface\DatasetMapReduce as iDatasetMapReduce;
use rdfInterface\DatasetCompare as iDatasetCompare;

/**
 * Description of Graph
 *
 * @author zozlak
 */
class Dataset implements iDataset, iDatasetMapReduce, iDatasetCompare {

    /**
     *
     * @var array<int, iQuad>
     */
    private array $quads = [];

    public function __construct() {
        
    }

    public function __toString(): string {
        $ret = '';
        foreach ($this->quads as $i) {
            $ret .= $i . "\n";
        }
        return $ret;
    }

    public function equals(iDataset $other): bool {
        $n = 0;
        // $other contained in $this
        foreach ($other as $i) {
            if (!($i->getSubject() instanceof iBlankNode)) {
                if (!$this->offsetExists($i)) {
                    return false;
                }
                $n++;
            }
        }
        // $this contained in $other
        foreach ($this as $i) {
            if (!($i->getSubject() instanceof iBlankNode)) {
                $n--;
            }
        }
        return $n === 0;
    }

    public function add(iQuad | iQuadIterator $quads): void {
        if ($quads instanceof iQuad) {
            $quads = [$quads];
        }
        foreach ($quads as $i) {
            $match = false;
            foreach ($this->quads as $j) {
                if ($j->equals($i)) {
                    $match = true;
                    break;
                }
            }
            if (!$match) {
                $this->quads[] = $i;
            }
        }
    }

    public function copy(iQuad | iQuadTemplate | iQuadIterator | callable | null $filter = null): iDataset {
        $dataset = new Dataset();
        try {
            foreach ($this->findMatchingQuads($filter) as $i) {
                $dataset->add($this->quads[$i]);
            }
        } catch (OutOfBoundsException) {
            
        }
        return $dataset;
    }

    public function copyExcept(iQuad | iQuadTemplate | iQuadIterator | callable | null $filter = null): iDataset {
        $dataset = new Dataset();
        foreach ($this->findNotMatchingQuads($filter) as $i) {
            $dataset->add($this->quads[$i]);
        }
        return $dataset;
    }

    public function union(iQuad | iQuadIterator $other): iDataset {
        $ret = new Dataset();
        $ret->add($this);
        $ret->add($other);
        return $ret;
    }

    public function xor(iQuad | iQuadIterator $other): iDataset {
        $ret = $this->union($other);
        $ret->delete($this->copy($other));
        return $ret;
    }

    public function delete(iQuad | iQuadTemplate | iQuadIterator | callable $filter): iDataset {
        $deleted = new Dataset();
        try {
            foreach ($this->findMatchingQuads($filter) as $i) {
                $deleted->add($this->quads[$i]);
                unset($this->quads[$i]);
            }
        } catch (OutOfBoundsException) {
            
        }
        return $deleted;
    }

    public function deleteExcept(iQuad | iQuadTemplate | iQuadIterator | callable $filter): iDataset {
        $deleted = new Dataset();
        foreach ($this->findNotMatchingQuads($filter) as $i) {
            $deleted->add($this->quads[$i]);
            unset($this->quads[$i]);
        }
        return $deleted;
    }

    public function forEach(callable $fn): void {
        foreach ($this->quads as $n => $i) {
            $this->quads[$n] = $fn($i, $this);
        }
    }

    // QuadIterator

    public function current(): iQuad {
        $cur = current($this->quads);
        if ($cur === false) {
            throw new OutOfBoundsException();
        }
        return $cur;
    }

    public function key() {
        $key = key($this->quads);
        if ($key === null) {
            throw new OutOfBoundsException();
        }
        return $key;
    }

    public function next(): void {
        next($this->quads);
    }

    public function rewind(): void {
        reset($this->quads);
    }

    public function valid(): bool {
        return key($this->quads) !== null;
    }

    // Countable

    public function count(): int {
        return count($this->quads);
    }
    // ArrayAccess

    /**
     *
     * @param iQuad|iQuadTemplate|callable $offset
     * @return bool
     */
    public function offsetExists($offset): bool {
        return $this->exists($offset);
    }

    private function exists(iQuad | iQuadTemplate | callable $offset): bool {
        try {
            $iter = $this->findMatchingQuads($offset);
            $this->checkIteratorEnd($iter);
            return true;
        } catch (OutOfBoundsException) {
            return false;
        }
    }

    /**
     *
     * @param iQuad|iQuadTemplate|callable $offset
     * @return iQuad
     */
    public function offsetGet($offset): iQuad {
        return $this->get($offset);
    }

    private function get(iQuad | iQuadTemplate | callable $offset): iQuad {
        $iter = $this->findMatchingQuads($offset);
        $idx  = $iter->current();
        $this->checkIteratorEnd($iter);
        return $this->quads[$idx];
    }

    /**
     *
     * @param iQuad|iQuadTemplate|callable|null $offset
     * @param iQuad $value
     * @return void
     */
    public function offsetSet($offset, $value): void {
        if ($offset === null) {
            $this->add($value);
        } else {
            $this->set($offset, $value);
        }
    }

    private function set(iQuad | iQuadTemplate | callable $offset, iQuad $value): void {
        $iter = $this->findMatchingQuads($offset);
        $idx  = $iter->current();
        $this->checkIteratorEnd($iter);
        if (!$this->quads[$idx]->equals($value)) {
            unset($this->quads[$idx]);
            $this->add($value);
        }
    }

    /**
     *
     * @param iQuad|iQuadTemplate|callable $offset
     * @return void
     */
    public function offsetUnset($offset): void {
        try {
            $iter  = $this->findMatchingQuads($offset);
            $match = $iter->current();
        } catch (OutOfBoundsException) {
            
        }
        if (isset($match) && isset($iter)) {
            $this->checkIteratorEnd($iter);
            array_splice($this->quads, $match, 1);
        }
    }

    /**
     *
     * @param iQuad|iQuadTemplate|iQuadIterator|callable|null $offset
     * @return Generator<int>
     * @throws OutOfBoundsException
     */
    private function findMatchingQuads(
        iQuad | iQuadTemplate | iQuadIterator | callable | null $offset
    ): Generator {
        $fn = $this->prepareMatchFunction($offset ?? true);
        $n  = 0;
        foreach ($this->quads as $i => $q) {
            if ($fn($q, $this)) {
                $n++;
                yield $i;
            }
        }
        if ($n === 0) {
            throw new OutOfBoundsException();
        }
    }

    /**
     *
     * @param iQuad|iQuadTemplate|iQuadIterator|callable|null $offset
     * @return Generator<int>
     */
    private function findNotMatchingQuads(
        iQuad | iQuadTemplate | iQuadIterator | callable | null $offset
    ): Generator {
        $fn = $this->prepareMatchFunction($offset ?? false);
        foreach ($this->quads as $i => $q) {
            if (!$fn($q, $this)) {
                yield $i;
            }
        }
    }

    private function prepareMatchFunction(iQuad | iQuadTemplate | iQuadIterator | callable | bool $offset): callable {
        $fn = function() use ($offset) {
            return $offset;
        };
        if (is_callable($offset)) {
            $fn = $offset;
        } elseif ($offset instanceof iQuad || $offset instanceof iQuadTemplate) {
            $fn = function(iQuad $x) use($offset): bool {
                return $offset->equals($x);
            };
        } elseif ($offset instanceof iQuadIterator) {
            $fn = function(iQuad $x) use($offset): bool {
                foreach ($offset as $i) {
                    if ($i->equals($x)) {
                        return true;
                    }
                }
                return false;
            };
        }
        return $fn;
    }

    /**
     * 
     * @param Iterator<int> $i
     * @return void
     * @throws OutOfBoundsException
     */
    private function checkIteratorEnd(Iterator $i): void {
        $i->next();
        if ($i->key() !== null) {
            throw new OutOfBoundsException("More than one quad matched");
        }
    }

    public function map(callable $fn): iDataset {
        $ret = new Dataset();
        foreach ($this as $i) {
            $ret->add($fn($i, $this));
        }
        return $ret;
    }

    public function reduce(callable $fn, $initialValue = null): mixed {
        foreach ($this as $i) {
            $initialValue = $fn($initialValue, $i, $this);
        }
        return $initialValue;
    }

    public function any(iQuad | iQuadTemplate | iQuadIterator | callable $filter): bool {
        try {
            $iter = $this->findMatchingQuads($filter);
            $iter = $iter->current(); // so PHP doesn't optimize previous line out
            return true;
        } catch (OutOfBoundsException) {
            return false;
        }
    }

    public function every(iQuad | iQuadTemplate | callable $filter): bool {
        try {
            $n = 0;
            foreach ($this->findMatchingQuads($filter) as $i) {
                $n++;
            }
            return $n === $this->count();
        } catch (OutOfBoundsException) {
            return false;
        }
    }

    public function none(iQuad | iQuadTemplate | iQuadIterator | callable $filter): bool {
        try {
            $iter = $this->findMatchingQuads($filter);
            $iter = $iter->current(); // so PHP doesn't optimize previous line out
            return false;
        } catch (OutOfBoundsException) {
            return true;
        }
    }
}