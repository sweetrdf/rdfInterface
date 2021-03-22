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
        
        $q1raw = DataFactory::quad(new NamedNode('http://a'), new NamedNode('http://b'), $l1raw);
        $q2raw = DataFactory::quad(new NamedNode('http://a'), new NamedNode('http://b'), $l1string);

        $d->add($q1raw);
        $d->add($q2raw);
        $this->assertEquals(1, count($d));
        $this->assertTrue(current($d)->getObject()->getValue() === '1');
        $this->assertTrue(current($d)->getObject()->getValue() === 1);
    }
}