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

use rdfInterface\DataFactory as iDataFactory;
use rdfInterface\Term as iTerm;
use rdfInterface\BlankNode as iBlankNode;
use rdfInterface\NamedNode as iNamedNode;
use rdfInterface\Literal as iLiteral;
use rdfInterface\DefaultGraph as iDefaultGraph;
use rdfInterface\Quad as iQuad;
use rdfInterface\QuadTemplate as iQuadTemplate;

/**
 * Description of LoggerTest
 *
 * @author zozlak
 */
abstract class DataFactoryTest extends \PHPUnit\Framework\TestCase {

    /**
     * Initialize to an object implementing the rdfInterface\DataFactory interface
     * in the setUpBeforeClass() method.
     * @var iDataFactory
     */
    protected static iDataFactory $df;

    public function testCreateBasic(): void {
        $bn = self::$df::blankNode();
        $nn = self::$df::namedNode('foo');
        $l  = self::$df::literal('foo', 'lang');
        $dg = self::$df::defaultGraph();
        $q  = self::$df::quad($bn, $nn, $l, $dg);
        $qt = self::$df::quadTemplate($bn, $nn, $l, $dg);

        $this->assertInstanceOf(iTerm::class, $bn);
        $this->assertInstanceOf(iTerm::class, $nn);
        $this->assertInstanceOf(iTerm::class, $l);
        $this->assertInstanceOf(iTerm::class, $dg);
        $this->assertInstanceOf(iTerm::class, $q);
        $this->assertInstanceOf(iTerm::class, $qt);
        $this->assertInstanceOf(iBlankNode::class, $bn);
        $this->assertInstanceOf(iNamedNode::class, $nn);
        $this->assertInstanceOf(iLiteral::class, $l);
        $this->assertInstanceOf(iDefaultGraph::class, $dg);
        $this->assertInstanceOf(iQuad::class, $q);
        $this->assertInstanceOf(iQuadTemplate::class, $qt);
    }
}
