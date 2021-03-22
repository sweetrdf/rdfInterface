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
 * Extends Term because of RDF*
 *
 * Remarks:
 * - getValue() should throw an error
 * - null return types in getSubject(), getPredicate() and getObject() make it
 *   possible for QuadTemplate to extend this interface
 *
 * @author zozlak
 */
interface Quad extends Term, TermCompare {

    /**
     *
     * @param Term $subject
     * @param NamedNode $predicate
     * @param Term $object
     * @param NamedNode|BlankNode|DefaultGraph|null $graph
     */
    public function __construct(
        Term $subject, NamedNode $predicate, Term $object,
        NamedNode | BlankNode | DefaultGraph | null $graph = null
    );

    public function getSubject(): Term;

    public function getPredicate(): NamedNode;

    public function getObject(): Term;

    /**
     * Null is not allowed to deal with the ambiguity between DefaultGraph and
     * null which mean the same (although it should be noted that all quads in
     * NamedNode/BlankNode graphs also belong to the DefaultGraph).
     * @return NamedNode|BlankNode|DefaultGraph
     */
    public function getGraph(): NamedNode | BlankNode | DefaultGraph;

    public function withSubject(Term $subject): Quad;

    public function withPredicate(NamedNode $predicate): Quad;

    public function withObject(Term $object): Quad;

    public function withGraph(NamedNode | BlankNode | DefaultGraph | null $graph): Quad;
}
