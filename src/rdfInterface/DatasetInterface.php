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
 * @extends \ArrayAccess<QuadInterface|QuadIteratorInterface|callable, QuadInterface>
 * @extends \IteratorAggregate<int, QuadInterface>
 */
interface DatasetInterface extends QuadIteratorInterface, \ArrayAccess, \Countable, \IteratorAggregate {

    public function __construct();

    public function __toString(): string;

    public function equals(DatasetInterface $other): bool;

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
     * @param QuadCompareInterface|QuadIteratorInterface|callable|null $filter
     * @return DatasetInterface
     * @see deleteExcept
     */
    public function copy(QuadCompareInterface | QuadIteratorInterface | callable | null $filter = null): DatasetInterface;

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
     * @param QuadCompareInterface|QuadIteratorInterface|callable|null $filter
     * @return DatasetInterface
     * @see delete()
     */
    public function copyExcept(QuadCompareInterface | QuadIteratorInterface | callable | null $filter): DatasetInterface;

    /**
     * Returns a new dataset being a union of the current one and the $other one.
     * 
     * For in-place union use add().
     * 
     * @param QuadInterface|QuadIteratorInterface $other
     * @return DatasetInterface
     * @see add()
     */
    public function union(QuadInterface | QuadIteratorInterface $other): DatasetInterface;

    /**
     * Returns a dataset being a symmetric difference of the current dataset and
     * the $other one.
     * 
     * There is no in-place equivalent.
     * 
     * @param QuadInterface|QuadIteratorInterface $other
     * @return DatasetInterface
     */
    public function xor(QuadInterface | QuadIteratorInterface $other): DatasetInterface;

    // In-place set operations

    /**
     * Adds quad(s) to the dataset.
     *
     * @param QuadInterface|QuadIteratorInterface $quads
     * @return void
     */
    public function add(QuadInterface | QuadIteratorInterface $quads): void;

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
     * @param QuadCompareInterface|QuadIteratorInterface|callable $filter
     * @return DatasetInterface a dataset containing removed quads.
     * @see copyExcept()
     */
    public function delete(QuadCompareInterface | QuadIteratorInterface | callable $filter): DatasetInterface; // callable(Quad, DatasetInterface)

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
     * @param QuadCompareInterface|QuadIteratorInterface|callable $filter
     * @return DatasetInterface a dataset containing removed quads.
     * @see copy()
     */
    public function deleteExcept(QuadCompareInterface | QuadIteratorInterface | callable $filter): DatasetInterface;
    // In-place modification

    /**
     * Iterates trough all quads replacing them with a callback result.
     * 
     * If the callback returns null, the quad should be removed from the dataset.
     * 
     * @param callable $fn with signature `fn(Quad, Dataset): ?Quad` to be run 
     *   an all quads
     * @param QuadCompareInterface|QuadIteratorInterface|callable $filter
     * @return void
     */
    public function forEach(callable $fn,
                            QuadCompareInterface | QuadIteratorInterface | callable $filter = null): void;

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
     * @param QuadCompareInterface|callable $offset
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
     *   `true`. If more than one quad is matched \OutOfBoundsException must be 
     *   thrown.
     * 
     * @param QuadCompareInterface|callable $offset
     * @return QuadInterface
     * @throws \OutOfBoundsException
     * @throws \OutOfRangeException
     */
    public function offsetGet(mixed $offset): QuadInterface;

    /**
     * Assigns a new value to the quad matching the $offset.
     * 
     * Offset can be specified as:
     * 
     * - An object implementing the \rdfInterface\QuadCompare interface.
     *   If more than one quad is matched \OutOfBoundsException must be thrown.
     * - A callable with the `fn(\rdfInterface\Quad, \rdfInterface\Dataset): bool`
     *   signature. Matching quads are the ones for which the callable returns
     *   `true`. If more than one quad is matched \OutOfBoundsException must be 
     *   thrown.
     * 
     * @param QuadCompareInterface|callable $offset
     * @param QuadInterface $value
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
     *   `true`.If more than one quad is matched \OutOfBoundsException must be 
     *   thrown.
     * 
     * @param QuadCompareInterface|callable $offset
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
     * @return QuadInterface | null
     */
    public function current(): QuadInterface | null;
    
    /**
     * Returns QuadIteratorInterface iterating over dataset's quads.
     * 
     * If $filter is provided, the iterator includes only quads matching the
     * filter.
     * 
     * $filter can be specified as:
     * 
     * - An object implementing the \rdfInterface\QuadCompare interface
     *   (e.g. a single Quad)
     * - An object implementing the \rdfInterface\QuadIterator interface
     *   (e.g. another Dataset)
     * - A callable with signature `fn(\rdfInterface\Quad, \rdfInterface\Dataset): bool`
     *   All quads for which the callable returns true are copied.
     *      * 
     * @param QuadCompareInterface|QuadIteratorInterface|callable|null $filter
     * @return QuadIteratorInterface
     */
    public function getIterator(QuadCompareInterface | QuadIteratorInterface | callable | null $filter = null): QuadIteratorInterface;
}
