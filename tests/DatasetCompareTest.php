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

/**
 * Description of LoggerTest
 *
 * @author zozlak
 */
abstract class DatasetCompareTest extends \PHPUnit\Framework\TestCase {

    use TestBaseTrait;

    abstract public static function getDataset(): DatasetCompare;

    public function testAnyNone(): void {
        $d1 = static::getDataset();
        $d1->add(new GenericQuadIterator(self::$quads));

        // Quad
        $this->assertTrue($d1->any(self::$quads[0]));
        $this->assertFalse($d1->none(self::$quads[0]));
        $this->assertFalse($d1->any(self::$quads[0]->withSubject(self::$df::namedNode('aaa'))));
        $this->assertTrue($d1->none(self::$quads[0]->withSubject(self::$df::namedNode('aaa'))));

        // QuadTemplate
        $this->assertTrue($d1->any(self::$df::quadTemplate(self::$df::namedNode('foo'))));
        $this->assertFalse($d1->none(self::$df::quadTemplate(self::$df::namedNode('foo'))));
        $this->assertFalse($d1->any(self::$df::quadTemplate(self::$df::namedNode('aaa'))));
        $this->assertTrue($d1->none(self::$df::quadTemplate(self::$df::namedNode('aaa'))));

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
        $this->assertTrue($d1->every(self::$df::quadTemplate(self::$df::namedNode('foo'))));
        $this->assertFalse($d1->none(self::$df::quadTemplate(null, null, self::$df::literal('baz', 'en'))));

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
}
