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

use Traversable;

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
     * Creates a DatasetNodeInterface object.
     * 
     * @param TermInterface|null $node Node has to be provided. If it is null,
     *   \BadMethodCallException should be thrown (null is accepted in the signature
     *   only to match the rdfInterface\Dataset::factory() signature). $node
     *   does not have to exist in the $dataset.
     * @param QuadIteratorAggregateInterface | QuadIteratorInterface | null $quads
     *   Quads to be stored in the dataset. Quads are kept internally no matter
     *   if their subject matches the $node. Note that
     *   - DatasetInterface can be passed as well as it implements 
     *     the QuadIteratorAggregateInterface.
     *   - Changes to the object passed as $quads will not modify the content
     *     of the created DatasetNodeInterface object. If you want this behaviour,
     *     uset the withDataset() method instead.
     * @return DatasetNodeInterface
     * @throws \BadMethodCallException
     * @see DatasetNodeInterface::withDataset()
     */
    static public function factory(TermInterface | null $node = null,
                                   QuadIteratorAggregateInterface | QuadIteratorInterface | null $quads = null): DatasetNodeInterface;

    /**
     * The actual dataset (and not its copy) should be returned.
     * This means if quads are in-place added/removed from the returned object,
     * these changes are shared with the DatasetNodeInterface object.
     * 
     * @return DatasetInterface
     */
    public function getDataset(): DatasetInterface;

    public function getNode(): TermInterface;

    /**
     * 
     * @param DatasetInterface $dataset Replaces the underlaying dataset with
     *   a given one. The $dataset object should be used directly so any
     *   in-place modification performed on it are reflected in 
     *   the DatasetNodeInterface object and vice versa.
     * @return DatasetNodeInterface
     */
    public function withDataset(DatasetInterface $dataset): DatasetNodeInterface;

    public function withNode(TermInterface $node): DatasetNodeInterface;

    public function equals(DatasetInterface | TermCompareInterface | DatasetNodeInterface $termOrDataset): bool;

    /**
     * Adds quad(s) to the dataset.
     * 
     * Does not check if the quad subject matches the DatasetNodeInterface's object node.
     * 
     * Allows passing QuadNoSubjectInterface. If their subject is null, the dataset's node
     * should be set as a subject. 
     *
     * @param QuadInterface|QuadNoSubjectInterface|Traversable<QuadInterface|QuadNoSubjectInterface>|array<QuadInterface|QuadNoSubjectInterface> $quads
     * @return void
     */
    public function add(QuadInterface | QuadNoSubjectInterface | Traversable | array $quads): void;

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
     * @param callable $fn function applied to every quad with signature `fn(quad, datasetNode)`
     * @param QuadCompareInterface|QuadIteratorInterface|QuadIteratorAggregateInterface|callable|null $filter
     * @return DatasetNodeInterface
     */
    public function map(callable $fn,
                        QuadCompareInterface | QuadIteratorInterface | QuadIteratorAggregateInterface | callable | null $filter = null): DatasetNodeInterface;
}
