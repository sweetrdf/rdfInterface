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
 * @extends \ArrayAccess<Quad|QuadIterator|callable, Quad>
 */
interface Dataset extends QuadIterator, \ArrayAccess, \Countable {

    public function __construct();

    public function __toString(): string;

    public function equals(Dataset $other): bool;

    // Immutable set operations

    /**
     * Creates a copy of the dataset.
     * 
     * If $filter is provided, the copy contains only quads matching the $filter.
     * 
     * $filter can be specified as:
     * 
     * - An object implementing the \rdfInterface\QuadCompare interface
     *   (e.g. a single Quad)
     * - An object implementing the \rdfInterface\QuadIterator interface
     *   (e.g. another Dataset)
     * - A callable with signature `fn(\rdfInterface\Quad, \rdfInterface\Dataset): bool`
     *   All quads for which the callable returns true are copied.
     * 
     * An in-place equivalent of a call using the $filter is the deleteExcept() method.
     * 
     * @param QuadCompare|QuadIterator|callable|null $filter
     * @return Dataset
     * @see deleteExcept
     */
    public function copy(QuadCompare | QuadIterator | callable | null $filter = null): Dataset;

    /**
     * Creates a copy of the dataset.
     * 
     * If $filter is provided, the copy contains only quads not matching the 
     * $filter.
     * 
     * $filter can be specified as:
     * 
     * - An object implementing the \rdfInterface\QuadCompare interface
     *   (e.g. a single Quad)
     * - An object implementing the \rdfInterface\QuadIterator interface
     *   (e.g. another Dataset)
     * - A callable with signature `fn(\rdfInterface\Quad, \rdfInterface\Dataset): bool`
     *   All quads for which the callable returns false are copied.
     * 
     * An in-place equivalent of a call using the $filter is the delete() method.
     * 
     * @param QuadCompare|QuadIterator|callable|null $filter
     * @return Dataset
     * @see delete()
     */
    public function copyExcept(QuadCompare | QuadIterator | callable | null $filter): Dataset;

    /**
     * Returns a new dataset being a union of the current one and the $other one.
     * 
     * For in-place union use add().
     * 
     * @param Quad|QuadIterator $other
     * @return Dataset
     * @see add()
     */
    public function union(Quad | QuadIterator $other): Dataset;

    /**
     * Returns a dataset being a symmetric difference of the current dataset and
     * the $other one.
     * 
     * There is no in-place equivalent.
     * 
     * @param Quad|QuadIterator $other
     * @return Dataset
     */
    public function xor(Quad | QuadIterator $other): Dataset;

    // In-place set operations

    /**
     * Adds quad(s) to the dataset.
     *
     * @param Quad|QuadIterator $quads
     * @return void
     */
    public function add(Quad | QuadIterator $quads): void;

    /**
     * In-place removes quads from the dataset.
     * 
     * All quads matching the $filter parameter are removed.
     * 
     * $filter can be specified as:
     * 
     * - An object implementing the \rdfInterface\QuadCompare interface
     *   (e.g. a single Quad)
     * - An object implementing the \rdfInterface\QuadIterator interface
     *   (e.g. another Dataset)
     * - A callable with signature `fn(\rdfInterface\Quad, \rdfInterface\Dataset): bool`
     *   All quads for which the callable returns true are removed.
     * 
     * An immputable equivalent is the copyExcept($filter) method.
     * 
     * @param QuadCompare|QuadIterator|callable $filter
     * @return Dataset a dataset containing removed quads.
     * @see copyExcept()
     */
    public function delete(QuadCompare | QuadIterator | callable $filter): Dataset; // callable(Quad, Dataset)

    /**
     * In-place removes quads from the dataset.
     * 
     * All quads but ones matching the $filter parameter are removed.
     * 
     * $filter can be specified as:
     * 
     * - An object implementing the \rdfInterface\QuadCompare interface
     *   (e.g. a single Quad)
     * - An object implementing the \rdfInterface\QuadIterator interface
     *   (e.g. another Dataset)
     * - A callable with signature `fn(\rdfInterface\Quad, \rdfInterface\Dataset): bool`
     *   All quads for which the callable returns false are removed.
     * 
     * An immputable equivalent is the copy($filter) method.
     * 
     * @param QuadCompare|QuadIterator|callable $filter
     * @return Dataset a dataset containing removed quads.
     * @see copy()
     */
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
     * Checks if a given offset exists.
     * 
     * Offset can be specified as:
     * 
     * - An object implementing the \rdfInterface\QuadCompare interface.
     *   If more than one quad is matched \OutOfBoundsException must be thrown.
     * - A callable with the `fn(\rdfInterface\Quad, \rdfInterface\Dataset): bool`
     *   signature. Matching quads are the ones for which the callable returns
     *   `true`.
     *   If more than one quad is matched \OutOfBoundsException must be thrown.
     * 
     * @param QuadCompare|callable $offset
     * @return bool
     * @throws \OutOfBoundsException
     * @throws \OutOfRangeException
     */
    public function offsetExists($offset): bool;

    /**
     * Returns a quad matching the $offset.
     * 
     * The $offset can be specified as:
     * 
     * - An object implementing the \rdfInterface\QuadCompare interface.
     *   If more than one quad is matched \OutOfBoundsException must be thrown.
     * - A callable with the `fn(\rdfInterface\Quad, \rdfInterface\Dataset): bool`
     *   signature. Matching quads are the ones for which the callable returns
     *   `true`.
     *   If more than one quad is matched \OutOfBoundsException must be thrown.
     * 
     * @param QuadCompare|callable $offset
     * @return Quad
     * @throws \OutOfBoundsException
     * @throws \OutOfRangeException
     */
    public function offsetGet($offset): Quad;

    /**
     * Assigns a new value to the quad matching the $offset.
     * 
     * Offset can be specified as:
     * 
     * - An object implementing the \rdfInterface\QuadCompare interface.
     *   If more than one quad is matched \OutOfBoundsException must be thrown.
     * - A callable with the `fn(\rdfInterface\Quad, \rdfInterface\Dataset): bool`
     *   signature. Matching quads are the ones for which the callable returns
     *   `true`.
     *   If more than one quad is matched \OutOfBoundsException must be thrown.
     * 
     * @param QuadCompare|callable $offset
     * @param Quad $value
     * @return void
     */
    public function offsetSet($offset, $value): void;

    /**
     * Removes a quad matching the $offset.
     * 
     * Offset can be specified as:
     * 
     * - An object implementing the \rdfInterface\QuadCompare interface.
     *   If more than one quad is matched \OutOfBoundsException must be thrown.
     * - A callable with the `fn(\rdfInterface\Quad, \rdfInterface\Dataset): bool`
     *   signature. Matching quads are the ones for which the callable returns
     *   `true`.
     *   If more than one quad is matched \OutOfBoundsException must be thrown.
     * 
     * @param QuadCompare|callable $offset
     * @return void
     */
    public function offsetUnset($offset): void;

    /**
     * Returns the current quad.
     * 
     * No particular order of quad traversal is guaranteed.
     * 
     * It must not require calling rewind() for a call to current() to return
     * a quad after the dataset has been created and at least one quad has been
     * added to it.
     * 
     * If valid() returns false, this method must return null (it must not throw
     * an exception).
     * 
     * @return Quad | null
     */
    public function current(): Quad | null;
}
