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

use rdfHelpers\GenericQuadIterator;
use rdfInterface\Literal;
use rdfInterface\Quad;
use rdfInterface\DatasetCompare;
use rdfInterface\Term;
use rdfInterface\TermCompare;
use rdfInterface\QuadCompare;

/**
 * Description of LoggerTest
 *
 * @author zozlak
 */
abstract class DatasetCompareTest extends \PHPUnit\Framework\TestCase {

    use TestBaseTrait;

    abstract public static function getDataset(): DatasetCompare;

    abstract public static function getQuadTemplate(TermCompare | Term | null $subject = null,
                                                    TermCompare | Term | null $predicate = null,
                                                    TermCompare | Term | null $object = null,
                                                    TermCompare | Term | null $graph = null): QuadCompare;

    public function testAnyNone(): void {
        $d1 = static::getDataset();
        $d1->add(new GenericQuadIterator(self::$quads));

        // Quad
        $this->assertTrue($d1->any(self::$quads[0]));
        $this->assertFalse($d1->none(self::$quads[0]));
        $this->assertFalse($d1->any(self::$quads[0]->withSubject(self::$df::namedNode('aaa'))));
        $this->assertTrue($d1->none(self::$quads[0]->withSubject(self::$df::namedNode('aaa'))));

        // QuadTemplate
        $this->assertTrue($d1->any(static::getQuadTemplate(self::$df::namedNode('foo'))));
        $this->assertFalse($d1->none(static::getQuadTemplate(self::$df::namedNode('foo'))));
        $this->assertFalse($d1->any(static::getQuadTemplate(self::$df::namedNode('aaa'))));
        $this->assertTrue($d1->none(static::getQuadTemplate(self::$df::namedNode('aaa'))));

        // QuadIterator
        $d2   = static::getDataset();
        $d2[] = self::$quads[0];
        $this->assertTrue($d1->any($d2));
        $this->assertFalse($d1->none($d2));

        $d2   = static::getDataset();
        $d2[] = self::$quads[0]->withSubject(self::$df::namedNode('aaa'));
        $this->assertFalse($d1->any($d2));
        $this->assertTrue($d1->none($d2));

        // callable
        $fn = function(Quad $x): bool {
            return $x->getSubject()->getValue() === 'foo';
        };
        $this->assertTrue($d1->any($fn));
        $this->assertFalse($d1->none($fn));

        $fn = function(Quad $x): bool {
            return $x->getSubject()->getValue() === 'aaa';
        };
        $this->assertFalse($d1->any($fn));
        $this->assertTrue($d1->none($fn));
    }

    public function testEvery(): void {
        // Quad
        $d1   = static::getDataset();
        $d1[] = self::$quads[0];
        $this->assertTrue($d1->every(self::$quads[0]));
        $d1[] = self::$quads[1];
        $this->assertFalse($d1->every(self::$quads[0]));
        $this->assertFalse($d1->every(self::$quads[0]->withSubject(self::$df::namedNode('aaa'))));

        // QuadTemplate
        $d1   = static::getDataset();
        $d1[] = self::$quads[0];
        $d1[] = self::$quads[3];
        $this->assertTrue($d1->every(static::getQuadTemplate(self::$df::namedNode('foo'))));
        $this->assertFalse($d1->none(static::getQuadTemplate(null, null, self::$df::literal('baz', 'en'))));

        // callable
        $d1   = static::getDataset();
        $d1[] = self::$quads[0];
        $d1[] = self::$quads[3];
        $fn   = function(Quad $x): bool {
            return $x->getSubject()->getValue() === 'foo';
        };
        $this->assertTrue($d1->every($fn));
        $fn = function(Quad $x): bool {
            $obj = $x->getObject();
            return $obj instanceof Literal ? $obj->getLang() === 'en' : false;
        };
        $this->assertFalse($d1->every($fn));
    }

    public function testForeignTerms(): void {
        $nn = self::$df::namedNode('foo');
        $q  = self::$df::quad($nn, $nn, $nn);

        $fnn = self::$fdf::namedNode('foo');
        $fq  = self::$fdf::quad($fnn, $fnn, $fnn);
        $fqt = static::getQuadTemplate($fnn);
        $fc  = function($x) use($fqt) {
            return $fqt->equals($x);
        };

        $d = static::getDataset();
        $d->add($q);
        $this->assertTrue(isset($d[$fq]));
        foreach ([$fq, $fqt, $fc] as $i) { // add callable
            $this->assertTrue($d->any($i), "Tested class " . $fq::class);
            $this->assertTrue($d->every($i), "Tested class " . $fq::class);
            $this->assertFalse($d->none($i), "Tested class " . $fq::class);
        }
    }
}
