<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
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
