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

use rdfInterface\Literal;
use rdfInterface\Quad;
use rdfInterface\DatasetMapReduce;
use rdfInterface\Term;
use rdfInterface\TermCompare;
use rdfInterface\QuadCompare;

/**
 * Description of LoggerTest
 *
 * @author zozlak
 */
abstract class DatasetMapReduceTest extends \PHPUnit\Framework\TestCase {

    use TestBaseTrait;

    abstract public static function getDataset(): DatasetMapReduce;

    abstract public static function getQuadTemplate(TermCompare | Term | null $subject = null,
                                                    TermCompare | Term | null $predicate = null,
                                                    TermCompare | Term | null $object = null,
                                                    TermCompare | Term | null $graph = null): QuadCompare;

    public function testMap(): void {
        $d1   = static::getDataset();
        $d1[] = self::$df::quad(self::$df::namedNode('foo'), self::$df::namedNode('baz'), self::$df::literal(1));
        $d1[] = self::$df::quad(self::$df::namedNode('bar'), self::$df::namedNode('baz'), self::$df::literal(5));
        $d2   = $d1->map(function (Quad $x) {
            $obj = $x->getObject();
            return $obj instanceof Literal ? $x->withObject($obj->withValue((float) (string) $obj->getValue() * 2)) : $x;
        });
        $this->assertCount(2, $d1);
        $this->assertEquals(1, (int) (string) $d1[static::getQuadTemplate(self::$df::namedNode('foo'))]->getObject()->getValue());
        $this->assertEquals(5, (int) (string) $d1[static::getQuadTemplate(self::$df::namedNode('bar'))]->getObject()->getValue());
        $this->assertCount(2, $d2);
        $this->assertEquals(2, (int) (string) $d2[static::getQuadTemplate(self::$df::namedNode('foo'))]->getObject()->getValue());
        $this->assertEquals(10, (int) (string) $d2[static::getQuadTemplate(self::$df::namedNode('bar'))]->getObject()->getValue());
    }

    public function testReduce(): void {
        $d1   = static::getDataset();
        $d1[] = self::$df::quad(self::$df::namedNode('foo'), self::$df::namedNode('baz'), self::$df::literal(1));
        $d1[] = self::$df::quad(self::$df::namedNode('bar'), self::$df::namedNode('baz'), self::$df::literal(5));
        $sum  = $d1->reduce(function (float $sum, Quad $x) {
            return $sum + (float) (string) $x->getObject()->getValue();
        }, 0);
        $this->assertEquals(6, $sum);
        $this->assertCount(2, $d1);
        $this->assertEquals(1, (int) (string) $d1[static::getQuadTemplate(self::$df::namedNode('foo'))]->getObject()->getValue());
        $this->assertEquals(5, (int) (string) $d1[static::getQuadTemplate(self::$df::namedNode('bar'))]->getObject()->getValue());
    }
}
