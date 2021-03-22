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

use OutOfBoundsException;
use rdfHelpers\GenericQuadIterator;
use rdfInterface\Literal;
use rdfInterface\Quad;
use rdfInterface\Dataset;
use rdfInterface\QuadCompare;
use rdfInterface\Term;
use rdfInterface\TermCompare;

/**
 * Description of LoggerTest
 *
 * @author zozlak
 */
abstract class DatasetTest extends \PHPUnit\Framework\TestCase {

    use TestBaseTrait;

    abstract public static function getDataset(): Dataset;

    abstract public static function getForeignDataset(): Dataset; // foreign \rdfInterface\Dataset implementation

    abstract public static function getQuadTemplate(TermCompare | Term | null $subject = null,
                                                    TermCompare | Term | null $predicate = null,
                                                    TermCompare | Term | null $object = null,
                                                    TermCompare | Term | null $graph = null): QuadCompare;

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
        $tmpl = static::getQuadTemplate(self::$df::namedNode('bar'));
        $this->assertTrue(self::$quads[2]->equals($d[$tmpl]));
        try {
            $tmpl = static::getQuadTemplate(null, self::$df::namedNode('bar'));
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
        $tmpl     = static::getQuadTemplate(self::$df::namedNode('bar'), self::$df::namedNode('baz'));
        $d[$tmpl] = self::$quads[3];
        $this->assertCount(2, $d);
        $this->assertContains(self::$quads[3], $d);
        $this->assertNotContains(self::$quads[2], $d);
        try {
            // two quads match
            $d[static::getQuadTemplate(self::$df::namedNode('foo'))] = self::$quads[0];
            $this->assertTrue(false);
        } catch (OutOfBoundsException) {
            
        }
        try {
            // no quad matches
            $d[static::getQuadTemplate(self::$df::namedNode('bar'), self::$df::namedNode('foo'))] = self::$quads[0];
            $this->assertTrue(false);
        } catch (OutOfBoundsException) {
            
        }
        try {
            // no quad matches
            $d[static::getQuadTemplate(self::$df::namedNode('aaa'))] = self::$quads[0];
            $this->assertTrue(false);
        } catch (OutOfBoundsException) {
            
        }

        // by callback
        // 0 + foo bar "baz"
        // 1 - baz foo bar
        // 2 - bar baz foo
        // 3 + foo bar "baz"@en graph
        $fn = function(Quad $q, Dataset $d) {
            return $q->getGraph()->getValue() === 'graph';
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
        unset($d[static::getQuadTemplate(self::$quads[1]->getSubject())]);
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
        $d2   = $d1->copy(static::getQuadTemplate(self::$df::namedNode('foo')));
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
        $d2   = $d1->copyExcept(static::getQuadTemplate(self::$df::namedNode('foo')));
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
        $d2 = $d1->copyExcept(static::getQuadTemplate(self::$quads[0]->getSubject()));
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
        $d2->delete(static::getQuadTemplate(self::$df::namedNode('foo')));
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
        $d2->deleteExcept(static::getQuadTemplate(self::$df::namedNode('foo')));
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
        $this->assertEquals(2, (int) (string) $d[static::getQuadTemplate(self::$df::namedNode('foo'))]->getObject()->getValue());
        $this->assertEquals(10, (int) (string) $d[static::getQuadTemplate(self::$df::namedNode('bar'))]->getObject()->getValue());
    }

    public function testForeignTerms(): void {
        $nn = self::$df::namedNode('foo');
        $bn = self::$df::blankNode('bar');
        $l  = self::$df::literal('baz');
        $dg = self::$df::defaultGraph();
        $q  = self::$df::quad($nn, $nn, $nn);
        $q2 = self::$df::quad($nn, $nn, $bn);
        $q3 = self::$df::quad($nn, $nn, $l, $dg);

        $fnn = self::$fdf::namedNode('foo');
        $fbn = self::$fdf::blankNode('bar');
        $fl  = self::$fdf::literal('baz');
        $fdg = self::$fdf::defaultGraph();
        $fq  = self::$fdf::quad($fnn, $fnn, $fnn);
        $fq2 = self::$fdf::quad($fnn, $fnn, $fbn);
        $fq3 = self::$fdf::quad($fnn, $fnn, $fl, $fdg);
        $fqt = static::getQuadTemplate($fnn);
        $fqi = new GenericQuadIterator($fq);

        // add
        $d  = static::getDataset();
        $d->add(new GenericQuadIterator([$q, $q2, $q3]));
        $fd = static::getForeignDataset();
        $fd->add(new GenericQuadIterator([$fq, $fq2, $fq3]));
        $this->assertTrue($d->equals($fd));
        $this->assertTrue(isset($d[$fq]));
        $this->assertTrue(isset($d[$fq2]));
        $this->assertTrue(isset($d[$fq3]));

        $d->add($fq);
        $this->assertEquals(3, count($d));
        $this->assertTrue(isset($d[$fq]));
        $d->add($fqi);
        $this->assertEquals(3, count($d));
        $this->assertTrue(isset($d[$fq]));

        // base for other tests
        $d  = static::getDataset();
        $d->add($q);
        $fd = static::getDataset();
        $fd->add($q);

        // offsetSet
        $d[$fq]  = $fq;
        $this->assertEquals(1, count($d));
        $this->assertTrue(isset($d[$q]));
        $d[$fqt] = $q;
        $this->assertEquals(1, count($d));
        $this->assertTrue(isset($d[$fq]));

        // offsetSet as add
        $d   = static::getDataset();
        $d[] = $fq;
        $this->assertEquals(1, count($d));
        $this->assertTrue(isset($d[$q]));
        $d[] = $q;
        $this->assertEquals(1, count($d));
        $this->assertTrue(isset($d[$q]));

        // copy
        $d = static::getDataset();
        $d->add($q);
        foreach ([$fq, $fqt, $fqi] as $i) {
            $d2 = $d->copy($i);
            $this->assertEquals(1, count($d2), "Tested class " . $fq::class);
            $this->assertTrue(isset($d2[$q]), "Tested class " . $fq::class);
            $this->assertTrue($d2->equals($fd), "Tested class " . $fq::class);

            $d2->deleteExcept($i);
            $this->assertEquals(1, count($d2), "Tested class " . $fq::class);
            $this->assertTrue(isset($d2[$q]), "Tested class " . $fq::class);
            $this->assertTrue($d2->equals($fd), "Tested class " . $fq::class);

            $r = $d2->delete($i);
            $this->assertEquals(0, count($d2), "Tested class " . $fq::class);
            $this->assertTrue(isset($r[$q]), "Tested class " . $fq::class);

            $d3 = $d->copyExcept($i);
            $this->assertEquals(0, count($d3), "Tested class " . $fq::class);
        }

        // union / xor
        $d = static::getDataset();
        $d->add($q);

        $d2 = $d->union($fqi);
        $this->assertEquals(1, count($d2));
        $this->assertTrue(isset($d2[$q]));
        $this->assertTrue(isset($d2[$fqt]));
        $this->assertTrue($d2->equals($fd), "Tested class " . $fq::class);

        $d3 = $d->xor($fqi);
        $this->assertEquals(0, count($d3));
    }
}
