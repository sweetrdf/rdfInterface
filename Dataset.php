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
interface Dataset extends QuadIterator, \ArrayAccess, \Countable
{

    public function __construct();

    public function __toString(): string;

    public function equals(Dataset $other): bool;

    // edges management

    /**
     * Adds set of quads.
     *
     * Use array append syntax to append a single quad.
     *
     * @param Quad|QuadIterator $quads
     * @return void
     */
    public function add(Quad | QuadIterator $quads): void;

    public function delete(
        Quad | QuadIterator | callable $filter,
        bool $match = true
    ): Dataset; // callable(Quad, Dataset)

    /**
     *
     * @param callable $fn with signature `fn(Quad, Dataset): Quad` runs on each quad
     * @param Quad|callable|null $filter
     * @return void
     */
    public function forEach(callable $fn, Quad | callable | null $filter = null): void;

    public function copy(Quad | callable | null $filter = null, bool $match = true): Dataset;

    // ArrayAccess with typing

    /**
     *
     * @param int|Quad|QuadTemplate|callable $offset
     * @return bool
     */
    public function offsetExists($offset): bool;

    /**
     *
     * @param int|Quad|QuadTemplate|callable $offset
     * @return Quad|QuadIterator
     */
    public function offsetGet($offset): Quad | QuadIterator;

    /**
     *
     * @param int|Quad|QuadTemplate|callable $offset
     * @param Quad $value
     * @return void
     */
    public function offsetSet($offset, $value): void;

    /**
     *
     * @param int|Quad|QuadTemplate|callable $offset
     * @return void
     */
    public function offsetUnset($offset): void;
}
