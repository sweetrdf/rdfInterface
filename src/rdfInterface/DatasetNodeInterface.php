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
 * @extends \ArrayAccess<QuadInterface|QuadIteratorInterface|callable|int<0, 0>, QuadInterface>
 */
interface DatasetNodeInterface extends QuadInterface, DatasetInterface {

    /**
     * 
     * @param DatasetInterface $dataset
     * @param TermInterface $node the node has to be a subject in one of the $dataset triples.
     *   If it is not, \BadMethodCallException should be thrown
     * @throws \BadMethodCallException
     */
    static public function fromDataset(DatasetInterface $dataset,
                                       TermInterface $node): self;

    public function getDataset(): DatasetInterface;
}
