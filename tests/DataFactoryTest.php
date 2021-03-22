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

namespace rdfInterface\tests;

use rdfInterface\Term;
use rdfInterface\BlankNode;
use rdfInterface\NamedNode;
use rdfInterface\Literal;
use rdfInterface\DefaultGraph;
use rdfInterface\Quad;

/**
 * Description of LoggerTest
 *
 * @author zozlak
 */
abstract class DataFactoryTest extends \PHPUnit\Framework\TestCase {

    use TestBaseTrait;

    public function testCreateBlankNode(): void {
        $bn = self::$df::blankNode();
        $this->assertInstanceOf(Term::class, $bn);
        $this->assertInstanceOf(BlankNode::class, $bn);
    }

    public function testCreateNamedNode(): void {
        $nn = self::$df::namedNode('foo');
        $this->assertInstanceOf(Term::class, $nn);
        $this->assertInstanceOf(NamedNode::class, $nn);
    }

    public function testCreateLiteral(): void {
        $l = self::$df::literal('foo', 'lang');
        $this->assertInstanceOf(Term::class, $l);
        $this->assertInstanceOf(Literal::class, $l);
    }

    public function testCreateDefaultGraph(): void {
        $dg = self::$df::defaultGraph();
        $this->assertInstanceOf(Term::class, $dg);
        $this->assertInstanceOf(DefaultGraph::class, $dg);
    }

    public function testCreateQuad(): void {
        $bn = self::$df::blankNode();
        $nn = self::$df::namedNode('foo');
        $l = self::$df::literal('foo', 'lang');
        $dg = self::$df::defaultGraph();
        
        $q = self::$df::quad($bn, $nn, $l, $dg);
        $this->assertInstanceOf(Term::class, $q);
        $this->assertInstanceOf(Quad::class, $q);
    }
}
