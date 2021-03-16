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
use BadMethodCallException;
use rdfInterface\RdfNamespace;

/**
 * Description of RdfNamespaceTest
 *
 * @author zozlak
 */
abstract class RdfNamespaceTest extends \PHPUnit\Framework\TestCase {

    use TestBaseTrait;

    abstract public static function getRdfNamespace(): RdfNamespace;

    public function testGetAddRemove(): void {
        $nmsp = static::getRdfNamespace();

        $sn = $nmsp->add('http://foo');
        $this->assertArrayHasKey($sn, $nmsp->getAll());
        $this->assertEquals('http://foo', $nmsp->getAll()[$sn]);
        $this->assertEquals('http://foo', $nmsp->get($sn));

        $sn = $nmsp->add('http://bar', 'baz');
        $this->assertEquals('baz', $sn);
        $this->assertArrayHasKey('baz', $nmsp->getAll());
        $this->assertEquals('http://bar', $nmsp->getAll()['baz']);
        $this->assertEquals('http://bar', $nmsp->get('baz'));

        $sn = $nmsp->add('http://baz', 'baz');
        $this->assertEquals('baz', $sn);
        $this->assertArrayHasKey('baz', $nmsp->getAll());
        $this->assertEquals('http://baz', $nmsp->getAll()['baz']);
        $this->assertEquals('http://baz', $nmsp->get('baz'));

        $nmsp->remove('baz');
        $this->assertArrayNotHasKey('baz', $nmsp->getAll());
        try {
            $nmsp->get('baz');
            $this->assertTrue(false);
        } catch (OutOfBoundsException $ex) {
            
        }
    }

    public function testShorten(): void {
        $nmsp = static::getRdfNamespace();
        $nmsp->add('http://foo/', 'n1');

        $this->assertEquals('n1:bar', $nmsp->shorten(self::$df::namedNode('http://foo/bar'), false));

        $sn1 = $nmsp->shorten(self::$df::namedNode('http://bar#baz'), true);
        $p   = (int) strpos($sn1, ':');
        $this->assertStringEndsWith(':baz', $sn1);
        $sn2 = $nmsp->shorten(self::$df::namedNode('http://bar#foo'), false);
        $this->assertStringEndsWith(':foo', $sn2);
        $this->assertEquals(substr($sn1, 0, $p), substr($sn2, 0, $p));
        $sn3 = $nmsp->shorten(self::$df::namedNode('http://bar#foo/'), false);
        $this->assertStringEndsWith(':foo/', $sn3);
        $this->assertEquals(substr($sn1, 0, $p), substr($sn3, 0, $p));

        try {
            $nmsp->shorten(self::$df::namedNode('http://foobar/baz'), false);
            $this->assertTrue(false);
        } catch (OutOfBoundsException $ex) {
            
        }
    }

    public function testExpand(): void {
        $nmsp = static::getRdfNamespace();
        $nmsp->add('http://foo/', 'n1');
        $n2   = $nmsp->add('http://bar#');

        $this->assertEquals('http://foo/baz', $nmsp->expand('n1:baz')->getValue());
        $this->assertEquals('http://bar#baz', $nmsp->expand($n2 . ':baz')->getValue());

        try {
            $nmsp->expand('foobar:baz');
            $this->assertTrue(false);
        } catch (OutOfBoundsException $ex) {
            
        }

        try {
            $nmsp->expand('foobar');
            $this->assertTrue(false);
        } catch (BadMethodCallException $ex) {
            
        }
    }
}
