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

namespace rdfInterface;

/**
 * Main, edge(quad) and Dataset-oriented Dataset API
 *
 * @author zozlak
 * @extends \ArrayAccess<int|Quad|QuadIterator|callable, Quad>
 */
interface Dataset extends QuadIterator, \ArrayAccess, \Countable {

    public function __construct();

    public function __toString(): string;

    public function equals(Dataset $other): bool;

    // Immutable set operations

    public function copy(QuadCompare | QuadIterator | callable | null $filter = null): Dataset;

    public function copyExcept(QuadCompare | QuadIterator | callable | null $filter = null): Dataset;

    public function union(Quad | QuadIterator $other): Dataset;

    public function xor(Quad | QuadIterator $other): Dataset;

    // In-place set operations

    /**
     * Adds set of quads.
     *
     * Use array append syntax to append a single quad.
     *
     * @param Quad|QuadIterator $quads
     * @return void
     */
    public function add(Quad | QuadIterator $quads): void;

    public function delete(QuadCompare | QuadIterator | callable $filter): Dataset; // callable(Quad, Dataset)

    public function deleteExcept(QuadCompare | QuadIterator | callable $filter): Dataset; // callable(Quad, Dataset)
    // In-place modification

    /**
     * Iterates trough all quads replacing them with a callback result.
     * 
     * @param callable $fn with signature `fn(Quad, Dataset): Quad` runs on each quad
     * @return void
     */
    public function forEach(callable $fn): void;

    // ArrayAccess (with narrower types)

    /**
     *
     * @param QuadCompare|callable $offset
     * @return bool
     */
    public function offsetExists($offset): bool;

    /**
     *
     * @param QuadCompare|callable $offset
     * @return Quad
     */
    public function offsetGet($offset): Quad;

    /**
     *
     * @param QuadCompare|callable $offset
     * @param Quad $value
     * @return void
     */
    public function offsetSet($offset, $value): void;

    /**
     *
     * @param QuadCompare|callable $offset
     * @return void
     */
    public function offsetUnset($offset): void;
}
