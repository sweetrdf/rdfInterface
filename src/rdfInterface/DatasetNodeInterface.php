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
 * All methods inherited from the rdfInterface\DatasetInterface should work only
 * on the triples having a given node as a subject.
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

    public function getDataset(): DatasetInterface;

    public function getNode(): TermInterface;

    public function withDataset(): DatasetNodeInterface;

    public function withNode(): DatasetNodeInterface;

    public function equals(DatasetInterface | TermCompareInterface | DatasetNodeInterface $termOrDataset): bool;

    public function copy(QuadCompareInterface | QuadIteratorInterface | QuadIteratorAggregateInterface | callable | null $filter = null): DatasetNodeInterface;

    public function copyExcept(QuadCompareInterface | QuadIteratorInterface | QuadIteratorAggregateInterface | callable | null $filter): DatasetNodeInterface;

    public function delete(QuadCompareInterface | QuadIteratorInterface | QuadIteratorAggregateInterface | callable | null $filter): DatasetNodeInterface;

    public function deleteExcept(QuadCompareInterface | QuadIteratorInterface | QuadIteratorAggregateInterface | callable | null $filter): DatasetNodeInterface;

    public function union(QuadInterface | QuadIteratorInterface | QuadIteratorAggregateInterface $other): DatasetNodeInterface;

    public function xor(QuadInterface | QuadIteratorInterface | QuadIteratorAggregateInterface $other): DatasetNodeInterface;
}
