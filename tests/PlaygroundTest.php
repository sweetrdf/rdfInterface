<?php

namespace rdfInterface\tests;

use PHPUnit\Framework\TestCase;
use rdfInterface\ReferenceImplementation\BlankNode;
use rdfInterface\ReferenceImplementation\DataSet;
use rdfInterface\ReferenceImplementation\DataFactory;
use rdfInterface\ReferenceImplementation\NamedNode;

class PlaygroundTest extends TestCase
{
    public function test()
    {
        $l1raw = DataFactory::literal(1);
        $l1string = DataFactory::literal('1');

        $this->assertTrue($l1raw->getValue() === '1');
        $this->assertTrue($l1raw->equals($l1string));
        $this->assertFalse($l1raw->getValue() === 1);
        
        $this->assertTrue($l1string->getValue() === '1');
        $this->assertTrue($l1string->getValue() === '1');

        $d = new Dataset();
        
        $q1raw = DataFactory::quad(new BlankNode(), new NamedNode('http://a'), $l1raw);
        $q2raw = DataFactory::quad(new BlankNode(), new NamedNode('http://a'), $l1raw);

        $d->add($q1raw);
        $d->add($q2raw);
        $this->assertTrue(count($d) === 1);
        $this->assertTrue(current($d)->getObject()->getValue() === '1');
        $this->assertTrue(current($d)->getObject()->getValue() === 1);
    }
}