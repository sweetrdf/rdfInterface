<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace rdfInterface\tests;

use OutOfBoundsException;
use rdfHelpers\GenericQuadIterator;
use rdfInterface\DataFactory;
use rdfInterface\Literal;
use rdfInterface\Quad;
use rdfInterface\Dataset;

/**
 * Description of LoggerTest
 *
 * @author zozlak
 */
abstract class DatasetTest extends \PHPUnit\Framework\TestCase {

    abstract public static function getDataFactory(): DataFactory;

    abstract public static function getDataset(): Dataset;

    protected static DataFactory $df;

    /**
     *
     * @var array<Quad>
     */
    private static array $quads;

    public static function setUpBeforeClass(): void {
        self::$df    = static::getDataFactory();
        self::$quads = [
            self::$df::quad(self::$df::namedNode('foo'), self::$df::namedNode('bar'), self::$df::literal('baz')),
            self::$df::quad(self::$df::namedNode('baz'), self::$df::namedNode('foo'), self::$df::namedNode('bar')),
            self::$df::quad(self::$df::namedNode('bar'), self::$df::namedNode('baz'), self::$df::namedNode('foo')),
            self::$df::quad(self::$df::namedNode('foo'), self::$df::namedNode('bar'), self::$df::literal('baz', 'en'), self::$df::namedNode('graph')),
        ];
    }

    public function testAddQuads(): void {
        $d = static::getDataset();
        for ($i = 0; $i + 1 < count(self::$quads); $i++) {
            $d->add(self::$quads[$i]);
        }
        $this->assertEquals(3, count($d));

        $d->add(new GenericQuadIterator(self::$quads));
        $this->assertEquals(4, count($d));
    }

    public function testIterator(): void {
        $d = static::getDataset();
        $d->add(new GenericQuadIterator(self::$quads));
        foreach ($d as $k => $v) {
            $this->assertTrue($v->equals(self::$quads[$k]));
        }
    }

    public function testOffsetGetSmall(): void {
        $d      = static::getDataset();
        $d->add(new GenericQuadIterator(self::$quads));
        $triple = self::$df::quad(self::$df::namedNode('foo'), self::$df::namedNode('bar'), self::$df::literal('baz', 'de'));

        // by Quad
        foreach (self::$quads as $i) {
            $this->assertTrue(isset($d[$i]));
            $this->assertTrue($i->equals($d[$i]));
        }
        $this->assertFalse(isset($d[$triple]));
        try {
            $x = $d[$triple];
            $this->assertTrue(false);
        } catch (OutOfBoundsException) {
            
        }

        // by QuadTemplate
        $tmpl = self::$df::quadTemplate(self::$df::namedNode('bar'));
        $this->assertTrue(self::$quads[2]->equals($d[$tmpl]));
        try {
            $tmpl = self::$df::quadTemplate(null, self::$df::namedNode('bar'));
            $x    = $d[$tmpl];
            $this->assertTrue(false);
        } catch (OutOfBoundsException) {
            
        }

        // by callback
        $fn = function(Quad $q, Dataset $d) {
            return $q->getSubject()->getValue() === 'bar';
        };
        $this->assertTrue(self::$quads[2]->equals($d[$fn]));
        try {
            $fn = function(Quad $q, Dataset $d) {
                return $q->getPredicate()->getValue() === 'bar';
            };
            $x = $d[$fn];
            $this->assertTrue(false);
        } catch (OutOfBoundsException) {
            
        }
    }

    public function testOffsetSet(): void {
        $d   = static::getDataset();
        $d[] = self::$quads[0];
        $this->assertCount(1, $d);
        $this->assertContains(self::$quads[0], $d);

        $d[] = self::$quads[1];
        $d[] = self::$quads[2];
        $this->assertCount(3, $d);
        $this->assertContains(self::$quads[1], $d);
        $this->assertContains(self::$quads[2], $d);

        // by Quad
        // 0 + foo bar "baz"
        // 1 + baz foo bar
        // 2 + bar baz foo
        // 3 - foo bar "baz"@en graph
        $d[self::$quads[1]] = self::$quads[3];
        $this->assertCount(3, $d);
        $d[self::$quads[3]] = self::$quads[2];
        $this->assertCount(2, $d);
        $this->assertContains(self::$quads[0], $d);
        $this->assertContains(self::$quads[2], $d);
        $this->assertNotContains(self::$quads[1], $d);
        $this->assertNotContains(self::$quads[3], $d);
        try {
            $d[self::$quads[3]] = self::$quads[1];
            $this->assertTrue(false);
        } catch (OutOfBoundsException $ex) {
            
        }

        // by QuadTemplate
        // 0 + foo bar "baz"
        // 1 - baz foo bar
        // 2 + bar baz foo
        // 3 - foo bar "baz"@en graph
        $tmpl     = self::$df::quadTemplate(self::$df::namedNode('bar'), self::$df::namedNode('baz'));
        $d[$tmpl] = self::$quads[3];
        $this->assertCount(2, $d);
        $this->assertContains(self::$quads[3], $d);
        $this->assertNotContains(self::$quads[2], $d);
        try {
            // two quads match
            $d[self::$df::quadTemplate(self::$df::namedNode('foo'))] = self::$quads[0];
            $this->assertTrue(false);
        } catch (OutOfBoundsException) {
            
        }
        try {
            // no quad matches
            $d[self::$df::quadTemplate(self::$df::namedNode('bar'), self::$df::namedNode('foo'))] = self::$quads[0];
            $this->assertTrue(false);
        } catch (OutOfBoundsException) {
            
        }
        try {
            // no quad matches
            $d[self::$df::quadTemplate(self::$df::namedNode('aaa'))] = self::$quads[0];
            $this->assertTrue(false);
        } catch (OutOfBoundsException) {
            
        }

        // by callback
        // 0 + foo bar "baz"
        // 1 - baz foo bar
        // 2 - bar baz foo
        // 3 + foo bar "baz"@en graph
        $fn = function(Quad $q, Dataset $d) {
            return $q->getGraphIri()->getValue() === 'graph';
        };
        $d[$fn] = self::$quads[2];
        $this->assertCount(2, $d);
        $this->assertContains(self::$quads[2], $d);
        $this->assertNotContains(self::$quads[3], $d);
        $d[]    = self::$quads[3];
        try {
            // many matches
            $fn = function(Quad $q, Dataset $d) {
                return $q->getSubject()->getValue() === 'foo';
            };
            $d[$fn] = self::$quads[1];
            $this->assertTrue(false);
        } catch (OutOfBoundsException) {
            
        }
        try {
            // no match
            $fn = function(Quad $q, Dataset $d) {
                return $q->getSubject()->getValue() === 'aaa';
            };
            $d[$fn] = self::$quads[1];
            $this->assertTrue(false);
        } catch (OutOfBoundsException) {
            
        }
    }

    public function testOffsetUnSet(): void {
        $d  = static::getDataset();
        $d->add(new GenericQuadIterator(self::$quads));
        $this->assertCount(4, $d);
        // by Quad
        unset($d[self::$quads[0]]);
        $this->assertCount(3, $d);
        $this->assertNotContains(self::$quads[0], $d);
        // by QuadTemplate
        unset($d[self::$df::quadTemplate(self::$quads[1]->getSubject())]);
        $this->assertCount(2, $d);
        $this->assertNotContains(self::$quads[1], $d);
        // by callable
        $fn = function(Quad $x) {
            return $x->getSubject()->getValue() === 'bar';
        };
        unset($d[$fn]);
        $this->assertCount(1, $d);
        $this->assertNotContains(self::$quads[2], $d);
        // unset non-existent
        unset($d[self::$quads[0]]);
        $this->assertCount(1, $d);
        $this->assertContains(self::$quads[3], $d);
    }

    public function testToString(): void {
        $d   = static::getDataset();
        $d->add(self::$quads[0]);
        $d->add(self::$quads[1]);
        $ref = self::$quads[0] . "\n" . self::$quads[1] . "\n";
        $this->assertEquals($ref, (string) $d);
    }

    public function testEquals(): void {
        $d1 = static::getDataset();
        $d2 = static::getDataset();

        $d1[] = self::$quads[0];
        $d1[] = self::$quads[1];
        $d2[] = self::$quads[0];
        $d2[] = self::$quads[1];
        $this->assertTrue($d1->equals($d2));

        $d2[] = self::$quads[2];
        $this->assertFalse($d1->equals($d2));

        unset($d2[self::$quads[2]]);
        $this->assertTrue($d1->equals($d2));

        unset($d2[self::$quads[1]]);
        $this->assertFalse($d1->equals($d2));

        // blank nodes don't count
        $d2[] = self::$quads[1];
        $d1[] = self::$df::quad(self::$df::blankNode(), self::$df::namedNode('foo'), self::$df::literal('bar'));
        $this->assertTrue($d1->equals($d2));
        $d2[] = self::$df::quad(self::$df::blankNode(), self::$df::namedNode('bar'), self::$df::literal('baz'));
        $this->assertTrue($d1->equals($d2));
    }

    public function testCopy(): void {
        $d1 = static::getDataset();
        $d1->add(new GenericQuadIterator(self::$quads));

        // simple
        $d2 = $d1->copy();
        $this->assertTrue($d1->equals($d2));
        unset($d2[self::$quads[0]]);
        $this->assertCount(4, $d1);
        $this->assertCount(3, $d2);
        $this->assertFalse($d1->equals($d2));

        // Quad
        $d2 = $d1->copy(self::$quads[0]);
        $this->assertFalse($d1->equals($d2));
        $this->assertCount(1, $d2);
        $this->assertTrue(isset($d2[self::$quads[0]]));
        $this->assertFalse(isset($d2[self::$quads[1]]));

        // QuadTemplate
        $d2   = $d1->copy(self::$df::quadTemplate(self::$df::namedNode('foo')));
        $this->assertFalse($d1->equals($d2));
        $this->assertCount(2, $d2);
        $d2[] = self::$quads[1];
        $d2[] = self::$quads[2];
        $this->assertTrue($d1->equals($d2));

        // QuadIterator
        $d2 = $d1->copy($d1);
        $this->assertTrue($d1->equals($d2));

        // callable
        $fn = function(Quad $x): bool {
            return false;
        };
        $d2 = $d1->copy($fn);
        $this->assertFalse($d1->equals($d2));
        $this->assertCount(0, $d2);
    }

    public function testCopyExcept(): void {
        $d1 = static::getDataset();
        $d1->add(new GenericQuadIterator(self::$quads));

        // simple
        $d2 = $d1->copyExcept();
        $this->assertTrue($d1->equals($d2));

        // Quad
        $d2   = $d1->copyExcept(self::$quads[0]);
        $this->assertFalse($d1->equals($d2));
        $this->assertCount(3, $d2);
        $d2[] = self::$quads[0];
        $this->assertTrue($d1->equals($d2));

        // QuadTemplate
        $d2   = $d1->copyExcept(self::$df::quadTemplate(self::$df::namedNode('foo')));
        $this->assertFalse($d1->equals($d2));
        $this->assertCount(2, $d2);
        $d2[] = self::$quads[0];
        $d2[] = self::$quads[3];
        $this->assertTrue($d1->equals($d2));

        // QuadIterator
        $d2 = $d1->copyExcept($d1);
        $this->assertFalse($d1->equals($d2));
        $this->assertCount(0, $d2);

        // callable
        $fn = function(Quad $x): bool {
            return true;
        };
        $d2 = $d1->copyExcept($fn);
        $this->assertFalse($d1->equals($d2));
        $this->assertCount(0, $d2);

        $d1 = static::getDataset();
        $d1->add(new GenericQuadIterator(self::$quads));
        $d2 = $d1->copyExcept(self::$df::quadTemplate(self::$quads[0]->getSubject()));
        $this->assertFalse($d1->equals($d2));
        $this->assertCount(2, $d2);
        $this->assertNotContains(self::$quads[0], $d2);
        $this->assertNotContains(self::$quads[3], $d2);
    }

    public function testDelete(): void {
        $d1 = static::getDataset();
        $d1->add(new GenericQuadIterator(self::$quads));

        // Quad
        $d2 = $d1->copy();
        $d2->delete(self::$quads[0]->withSubject(self::$df::blankNode()));
        $this->assertCount(4, $d2);
        $this->assertTrue($d2->equals($d1));

        $d2->delete(self::$quads[0]);
        $this->assertCount(3, $d2);
        $this->assertFalse($d2->equals($d1));
        $this->assertNotContains(self::$quads[0], $d2);

        // QuadTemplate
        $d2 = $d1->copy();
        $d2->delete(self::$df::quadTemplate(self::$df::namedNode('foo')));
        $this->assertCount(2, $d2);
        $this->assertFalse($d2->equals($d1));
        $this->assertNotContains(self::$quads[0], $d2);
        $this->assertNotContains(self::$quads[3], $d2);

        // QuadIterator
        $d2 = $d1->copy();
        $d2->delete($d1);
        $this->assertCount(0, $d2);
        $this->assertFalse($d2->equals($d1));

        // callable
        $fn = function(Quad $x): bool {
            return $x->getSubject()->getValue() === 'foo';
        };
        $d2 = $d1->copy();
        $d2->delete($fn);
        $this->assertCount(2, $d2);
        $this->assertFalse($d2->equals($d1));
        $this->assertNotContains(self::$quads[0], $d2);
        $this->assertNotContains(self::$quads[3], $d2);
    }

    public function testDeleteExcept(): void {
        $d1 = static::getDataset();
        $d1->add(new GenericQuadIterator(self::$quads));

        // Quad
        $d2 = $d1->copy();
        $d2->deleteExcept(self::$quads[0]->withSubject(self::$df::blankNode()));
        $this->assertCount(0, $d2);
        $this->assertFalse($d2->equals($d1));

        $d2 = $d1->copy();
        $d2->deleteExcept(self::$quads[0]);
        $this->assertCount(1, $d2);
        $this->assertFalse($d2->equals($d1));
        $this->assertTrue(isset($d2[self::$quads[0]]));
        $this->assertFalse(isset($d2[self::$quads[1]]));

        // QuadTemplate
        $d2 = $d1->copy();
        $d2->deleteExcept(self::$df::quadTemplate(self::$df::namedNode('foo')));
        $this->assertCount(2, $d2);
        $this->assertFalse($d2->equals($d1));
        $this->assertTrue(isset($d2[self::$quads[0]]));
        $this->assertFalse(isset($d2[self::$quads[1]]));
        $this->assertTrue(isset($d2[self::$quads[3]]));

        // QuadIterator
        $d2 = $d1->copy();
        $d2->deleteExcept($d1);
        $this->assertCount(4, $d2);
        $this->assertTrue($d2->equals($d1));

        // callable
        $fn = function(Quad $x): bool {
            return $x->getSubject()->getValue() === 'foo';
        };
        $d2 = $d1->copy();
        $d2->deleteExcept($fn);
        $this->assertCount(2, $d2);
        $this->assertFalse($d2->equals($d1));
        $this->assertTrue(isset($d2[self::$quads[0]]));
        $this->assertFalse(isset($d2[self::$quads[1]]));
        $this->assertTrue(isset($d2[self::$quads[3]]));
    }

    public function testUnion(): void {
        $d1   = static::getDataset();
        $d1[] = self::$quads[0];
        $d1[] = self::$quads[1];
        $d2   = static::getDataset();
        $d2[] = self::$quads[1];
        $d2[] = self::$quads[2];

        $d11 = $d1->copy();
        $d22 = $d2->copy();
        $d3  = $d1->union($d2);
        $this->assertCount(2, $d1);
        $this->assertCount(2, $d2);
        $this->assertCount(3, $d3);
        $this->assertTrue($d11->equals($d1));
        $this->assertTrue($d22->equals($d2));
        $this->assertFalse($d3->equals($d1));
        $this->assertFalse($d3->equals($d2));
    }

    public function testXor(): void {
        $d1   = static::getDataset();
        $d1[] = self::$quads[0];
        $d1[] = self::$quads[1];
        $d2   = static::getDataset();
        $d2[] = self::$quads[1];
        $d2[] = self::$quads[2];

        $d11 = $d1->copy();
        $d22 = $d2->copy();
        $d3  = $d1->xor($d2);
        $this->assertCount(2, $d1);
        $this->assertCount(2, $d2);
        $this->assertCount(2, $d3);
        $this->assertFalse($d3->equals($d1));
        $this->assertFalse($d3->equals($d2));
        $this->assertContains(self::$quads[0], $d3);
        $this->assertContains(self::$quads[2], $d3);
        $this->assertNotContains(self::$quads[1], $d3);
    }

    public function testForEach(): void {
        $d   = static::getDataset();
        $d[] = self::$df::quad(self::$df::namedNode('foo'), self::$df::namedNode('baz'), self::$df::literal(1));
        $d[] = self::$df::quad(self::$df::namedNode('bar'), self::$df::namedNode('baz'), self::$df::literal(5));
        $d->forEach(function (Quad $x): Quad {
            $obj = $x->getObject();
            return $obj instanceof Literal ? $x->withObject($obj->withValue((float) (string) $obj->getValue() * 2)) : $x;
        });
        $this->assertEquals(2, (int) (string) $d[self::$df::quadTemplate(self::$df::namedNode('foo'))]->getObject()->getValue());
        $this->assertEquals(10, (int) (string) $d[self::$df::quadTemplate(self::$df::namedNode('bar'))]->getObject()->getValue());
    }
}
