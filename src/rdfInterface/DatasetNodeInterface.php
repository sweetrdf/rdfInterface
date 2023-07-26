<?php

/*
 * The MIT License
 *
 * Copyright 2023 zozlak.
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
 * Node-oriented graph API interface.
 * 
 * Basically a union of the rdfInterface\QuadInterface and the rdfInterface\DatasetInterface.
 *
 * All methods inherited from the rdfInterface\DatasetInterface and returning 
 * the rdfInterface\DatasetNodeInterface should:
 * - process only quads having the DatasetNodeInterface's node as a subject
 * - preserve all other quads
 * 
 * @author zozlak
 */
interface DatasetNodeInterface extends TermInterface, DatasetInterface {

    /**
     * The $node doesn't have to exist in the $dataset.
     * 
     * If $dataset is not provided, an empty dataset should be used.
     */
    public static function factory(TermInterface $node,
                                   DatasetInterface | null $dataset = null): DatasetNodeInterface;

    /**
     * The actual dataset (and not its copy) should be returned.
     * This means if quads are in-place added/removed from the returned object,
     * these changes are shared with the DatasetNodeInterface object.
     * 
     * @return DatasetInterface
     */
    public function getDataset(): DatasetInterface;

    public function getNode(): TermInterface;

    public function withDataset(DatasetInterface $dataset): DatasetNodeInterface;

    public function withNode(TermInterface $node): DatasetNodeInterface;

    public function equals(DatasetInterface | TermCompareInterface | DatasetNodeInterface $termOrDataset): bool;

    /**
     * Adds quad(s) to the dataset.
     * 
     * Does not check if the quad subject matches the DatasetNodeInterface's object node.
     *
     * @param QuadInterface|QuadIteratorInterface|QuadIteratorAggregateInterface $quads
     * @return void
     */
    public function add(QuadInterface | QuadIteratorInterface | QuadIteratorAggregateInterface $quads): void;

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
     * - NULL. In such a case the quad in $value is just added to the underalying
     *   dataset without checking if its subject matches DatasetNodeInterface
     *   object's node.
     * 
     * @see DatasetInterface::offsetSet()
     * @param QuadCompareInterface|callable $offset
     * @param QuadInterface $value
     * @return void
     * @throws \OutOfBoundsException
     */
    public function offsetSet($offset, $value): void;

    public function copy(QuadCompareInterface | QuadIteratorInterface | QuadIteratorAggregateInterface | callable | null $filter = null): DatasetNodeInterface;

    public function copyExcept(QuadCompareInterface | QuadIteratorInterface | QuadIteratorAggregateInterface | callable $filter): DatasetNodeInterface;

    /**
     * Only those quads from $other which have subject matching the DatasetNodeInterface's node are added.
     * 
     * @param QuadInterface|QuadIteratorInterface|QuadIteratorAggregateInterface $other
     * @return DatasetNodeInterface
     */
    public function union(QuadInterface | QuadIteratorInterface | QuadIteratorAggregateInterface $other): DatasetNodeInterface;

    /**
     * The resulting dataset should contain:
     * - all quads of the DatasetNodeInterface with subject other than the node
     * - xor between triples of the DatasetNodeInterface with subject being the node
     *   and triples of the $other with subject being the node
     * 
     * @param QuadInterface|QuadIteratorInterface|QuadIteratorAggregateInterface $other
     * @return DatasetNodeInterface
     */
    public function xor(QuadInterface | QuadIteratorInterface | QuadIteratorAggregateInterface $other): DatasetNodeInterface;

    /**
     * Quads with subject other than DatasetNodeInterface's node should be
     * returned untouched.
     * 
     * @param callable $fn
     * @param QuadCompareInterface|QuadIteratorInterface|QuadIteratorAggregateInterface|callable|null $filter
     * @return DatasetNodeInterface
     */
    public function map(callable $fn,
                        QuadCompareInterface | QuadIteratorInterface | QuadIteratorAggregateInterface | callable | null $filter = null): DatasetNodeInterface;
}
