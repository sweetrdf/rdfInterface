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

use BadMethodCallException;
use zozlak\RdfConstants as RDF;
use rdfInterface\Literal;

/**
 * Description of TermsTest
 *
 * @author zozlak
 */
abstract class TermsTest extends \PHPUnit\Framework\TestCase {

    use TestBaseTrait;

    public function testNamedNode(): void {
        $n  = [
            0 => self::$df::namedNode('foo'),
            1 => self::$df::namedNode('foo'),
            2 => self::$df::namedNode('bar'),
        ];
        $bn = self::$df::blankNode('foo');
        foreach ($n as $i) {
            $this->assertTrue($i->equals($i));
            $this->assertFalse($i->equals($bn));
            $this->assertInstanceOf(\rdfInterface\NamedNode::class, $i);
            $this->assertIsString((string) $i);
        }
        $this->assertEquals('foo', $n[0]->getValue());
        $this->assertEquals('foo', $n[1]->getValue());
        $this->assertEquals('bar', $n[2]->getValue());

        $this->assertTrue($n[0]->equals($n[1]));
        $this->assertFalse($n[0]->equals($n[2]));

        $this->assertTrue($n[1]->equals($n[0]));
        $this->assertFalse($n[1]->equals($n[2]));

        $this->assertFalse($n[2]->equals($n[0]));
        $this->assertFalse($n[2]->equals($n[1]));
    }

    public function testForeignNamedNode(): void {
        $this->assertTrue(self::$df::namedNode('foo')->equals(self::$fdf::namedNode('foo')));
    }

    public function testBlankNode(): void {
        $n  = [
            0 => self::$df::blankNode(),
            1 => self::$df::blankNode(),
            2 => self::$df::blankNode('foo'),
            3 => self::$df::blankNode('_:foo'),
        ];
        $nn = self::$df::namedNode('_:foo');
        foreach ($n as $i) {
            $this->assertTrue($i->equals($i));
            $this->assertFalse($i->equals($nn));
            $this->assertInstanceOf(\rdfInterface\BlankNode::class, $i);
            $this->assertIsString((string) $i);
            $this->assertStringStartsWith('_:', $i->getValue());
        }

        $this->assertFalse($n[0]->equals($n[1]));
        $this->assertFalse($n[0]->equals($n[2]));
        $this->assertFalse($n[0]->equals($n[3]));

        $this->assertFalse($n[1]->equals($n[2]));
        $this->assertFalse($n[1]->equals($n[3]));

        $this->assertFalse($n[2]->equals($n[0]));
        $this->assertFalse($n[2]->equals($n[1]));
        $this->assertTrue($n[2]->equals($n[3]));

        $this->assertTrue($n[3]->equals($n[2]));
    }

    public function testForeignBlankNode(): void {
        $this->assertTrue(self::$df::blankNode('_:n1')->equals(self::$fdf::blankNode('_:n1')));
    }

    public function testLiteralFactory(): void {
        $l  = [
            0 => self::$df::literal('1'),
            1 => self::$df::literal('1', 'eng'),
            2 => self::$df::literal('1', null, RDF::XSD_STRING),
            3 => self::$df::literal('1', null, RDF::XSD_INTEGER),
            4 => self::$df::literal('1', 'deu'),
            5 => self::$df::literal('1', ''),
            6 => self::$df::literal(1),
        ];
        $nn = self::$df::NamedNode('1');
        foreach ($l as $i) {
            $this->assertInstanceOf(\rdfInterface\Literal::class, $i);
            $this->assertSame('1', $i->getValue());
            $this->assertSame('1', $i->getValue(Literal::CAST_LEXICAL_FORM));
            $this->assertTrue($i->equals($i));
            $this->assertFalse($i->equals($nn));
            $this->assertIsString((string) $i);
        }

        $this->assertNull($l[0]->getLang());
        $this->assertEquals('eng', $l[1]->getLang());
        $this->assertNull($l[2]->getLang());
        $this->assertNull($l[3]->getLang());
        $this->assertEquals('deu', $l[4]->getLang());
        $this->assertNull($l[5]->getLang());
        $this->assertNull($l[6]->getLang());

        $this->assertEquals(RDF::XSD_STRING, $l[0]->getDatatype());
        $this->assertEquals(RDF::RDF_LANG_STRING, $l[1]->getDatatype());
        $this->assertEquals(RDF::XSD_STRING, $l[2]->getDatatype());
        $this->assertEquals(RDF::XSD_INTEGER, $l[3]->getDatatype());
        $this->assertEquals(RDF::RDF_LANG_STRING, $l[4]->getDatatype());
        $this->assertEquals(RDF::XSD_STRING, $l[5]->getDatatype());
        $this->assertEquals(RDF::XSD_INTEGER, $l[6]->getDatatype());

        $l  = [
            0 => self::$df::literal(true),
            1 => self::$df::literal(false),
            2 => self::$df::literal(1.0),
            3 => self::$df::literal(1, null, 'foo'),
        ];
        $nn = self::$df::NamedNode('1');
        foreach ($l as $i) {
            $this->assertInstanceOf(\rdfInterface\Literal::class, $i);
            $this->assertSame($i->getValue(), $i->getValue(Literal::CAST_LEXICAL_FORM));
            $this->assertTrue($i->equals($i));
            $this->assertFalse($i->equals($nn));
            $this->assertIsString((string) $i);
            $this->assertNull($i->getLang());
        }
        $this->assertEquals(RDF::XSD_BOOLEAN, $l[0]->getDatatype());
        $this->assertEquals(RDF::XSD_BOOLEAN, $l[1]->getDatatype());
        $this->assertEquals(RDF::XSD_DECIMAL, $l[2]->getDatatype());
        $this->assertEquals('foo', $l[3]->getDatatype());
    }

    public function testLiteralEqual(): void {
        $l1 = self::$df::literal(1);
        $l2 = self::$df::literal(1, null, RDF::XSD_INTEGER);
        $l3 = self::$df::literal('1');
        $this->assertTrue($l1->equals($l2));
        $this->assertFalse($l1->equals($l3));
        $this->assertFalse($l2->equals($l3));

        $l1 = self::$df::literal(1, null, RDF::XSD_INTEGER);
        $l2 = self::$df::literal('1', null, RDF::XSD_INTEGER);
        $l3 = self::$df::literal('01', null, RDF::XSD_INTEGER);
        $l4 = self::$df::literal(1, null, RDF::XSD_INT);
        $this->assertTrue($l1->equals($l2));
        $this->assertFalse($l1->equals($l3));
        $this->assertFalse($l2->equals($l3));
        $this->assertFalse($l1->equals($l4));
        $this->assertFalse($l2->equals($l4));

        $objValue = new DummyStringable('01');
        $l1       = self::$df::literal($objValue);
        $l2       = self::$df::literal($objValue, 'eng');
        $l3       = self::$df::literal($objValue, null, RDF::XSD_INT);
        $l4       = self::$df::literal('01');
        $l5       = self::$df::literal('01', 'eng');
        $l6       = self::$df::literal('01', null, RDF::XSD_INT);
        $this->assertTrue($l1->equals($l4));
        $this->assertTrue($l2->equals($l5));
        $this->assertTrue($l3->equals($l6));
        $this->assertEquals('01', $l1->getValue());
        $this->assertEquals('01', $l2->getValue());
        $this->assertEquals('01', $l3->getValue());
        $this->assertFalse($l1->equals($l2));
        $this->assertFalse($l1->equals($l5));
        $this->assertFalse($l2->equals($l3));
        $this->assertFalse($l2->equals($l6));
    }

    public function testLiteralWith(): void {
        $l0 = self::$df::literal('1');

        $l1 = $l0->withValue('2');
        $this->assertEquals('2', $l1->getValue());
        $this->assertNull($l1->getLang());
        $this->assertEquals(RDF::XSD_STRING, $l1->getDatatype());

        $l2 = $l1->withLang('eng');
        $this->assertEquals('2', $l2->getValue());
        $this->assertEquals('eng', $l2->getLang());
        $this->assertEquals(RDF::RDF_LANG_STRING, $l2->getDatatype());

        $l3 = $l2->withDatatype(RDF::XSD_INT);
        $this->assertEquals('2', $l3->getValue());
        $this->assertNull($l3->getLang());
        $this->assertEquals(RDF::XSD_INT, $l3->getDatatype());

        $l4 = $l2->withDatatype(RDF::XSD_STRING);
        $this->assertEquals('2', $l4->getValue());
        $this->assertNull($l4->getLang());
        $this->assertEquals(RDF::XSD_STRING, $l4->getDatatype());

        $l5 = $l3->withLang('deu');
        $this->assertEquals('2', $l5->getValue());
        $this->assertEquals('deu', $l5->getLang());
        $this->assertEquals(RDF::RDF_LANG_STRING, $l5->getDatatype());

        $l6 = $l3->withLang(null);
        $this->assertEquals('2', $l6->getValue());
        $this->assertNull($l6->getLang());
        $this->assertEquals(RDF::XSD_INT, $l6->getDatatype());

        $l7 = $l2->withValue('3');
        $this->assertEquals('3', $l7->getValue());
        $this->assertEquals('eng', $l7->getLang());
        $this->assertEquals(RDF::RDF_LANG_STRING, $l7->getDatatype());

        $l8 = $l3->withValue('4');
        $this->assertEquals('4', $l8->getValue());
        $this->assertNull($l8->getLang());
        $this->assertEquals(RDF::XSD_INT, $l8->getDatatype());

        $l9 = $l2->withValue(9);
        $this->assertEquals('9', $l9->getValue());
        $this->assertNull($l9->getLang());
        $this->assertEquals(RDF::XSD_INTEGER, $l9->getDatatype());

        $l10 = $l2->withValue(7.3);
        $this->assertEquals(7.3, (float) $l10->getValue());
        $this->assertNull($l10->getLang());
        $this->assertEquals(RDF::XSD_DECIMAL, $l10->getDatatype());

        $l11 = $l2->withValue(true);
        $this->assertEquals(true, (bool) $l11->getValue());
        $this->assertNull($l11->getLang());
        $this->assertEquals(RDF::XSD_BOOLEAN, $l11->getDatatype());

        $l12 = $l2->withValue(false);
        $this->assertEquals(false, (bool) $l12->getValue());
        $this->assertNull($l12->getLang());
        $this->assertEquals(RDF::XSD_BOOLEAN, $l12->getDatatype());

        $l13 = $l2->withValue(5);
        $this->assertEquals(5, (int) $l13->getValue());
        $this->assertNull($l13->getLang());
        $this->assertEquals(RDF::XSD_INTEGER, $l13->getDatatype());

        $l14 = $l13->withLang('pol');
        $this->assertEquals('5', $l14->getValue());
        $this->assertEquals('pol', $l14->getLang());
        $this->assertEquals(RDF::RDF_LANG_STRING, $l14->getDatatype());

        // immutability
        $this->assertEquals('1', $l0->getValue());
        $this->assertNull($l0->getLang());
        $this->assertEquals(RDF::XSD_STRING, $l0->getDatatype());

        $this->assertEquals('2', $l1->getValue());
        $this->assertNull($l1->getLang());
        $this->assertEquals(RDF::XSD_STRING, $l1->getDatatype());

        $this->assertEquals('2', $l2->getValue());
        $this->assertEquals('eng', $l2->getLang());
        $this->assertEquals(RDF::RDF_LANG_STRING, $l2->getDatatype());

        $this->assertEquals('2', (string) $l3->getValue());
        $this->assertNull($l3->getLang());
        $this->assertEquals(RDF::XSD_INT, $l3->getDatatype());

        // exceptions
        try {
            $l0->withDatatype(RDF::RDF_LANG_STRING);
            $this->assertTrue(false);
        } catch (BadMethodCallException) {
            
        }
    }

    public function testLiteralStringable(): void {
        $l1 = self::$df::literal('foo', 'eng');
        $l2 = self::$df::literal($l1);
        $this->assertEquals((string) $l1, (string) $l2->getValue());
        $this->assertNull($l2->getLang());
        $this->assertEquals(RDF::XSD_STRING, $l2->getDatatype());

        $l3 = self::$df::literal(1, null, RDF::XSD_INT);
        $l4 = self::$df::literal($l3);
        $this->assertEquals((string) $l3, (string) $l4->getValue());
        $this->assertNull($l2->getLang());
        $this->assertEquals(RDF::XSD_STRING, $l4->getDatatype());
    }

    public function testForeignLiteral(): void {
        $this->assertTrue(self::$df::literal('foo')->equals(self::$fdf::literal('foo')));
        $this->assertTrue(self::$df::literal('foo', '')->equals(self::$fdf::literal('foo', null, RDF::XSD_STRING)));
        $this->assertTrue(self::$df::literal('foo', 'eng')->equals(self::$fdf::literal('foo', 'eng')));
        $this->assertTrue(self::$df::literal('1', null, RDF::XSD_INT)->equals(self::$fdf::literal(1, '', RDF::XSD_INT)));
    }

    public function testForeignDefaultGraph(): void {
        $this->assertTrue(self::$df::defaultGraph()->equals(self::$fdf::defaultGraph()));
    }

    public function testQuad(): void {
        $nn1 = self::$df::namedNode('foo');
        $nn2 = self::$df::namedNode('bar');
        $nn3 = self::$df::namedNode('baz');
        $l1  = self::$df::literal('foo');
        $l2  = self::$df::literal('foo', 'eng');
        $l3  = self::$df::literal('foo', '', RDF::XSD_STRING);
        $g1  = self::$df::defaultGraph();
        $g2  = self::$df::blankNode('foo');

        $q = [
            0 => self::$df::quad($nn3, $nn2, $nn1),
            1 => self::$df::quad($nn3, $nn2, $l1),
            2 => self::$df::quad($nn3, $nn2, $l2),
            3 => self::$df::quad($nn3, $nn2, $l3),
            4 => self::$df::quad($nn3, $nn2, $nn1, $g1),
            5 => self::$df::quad($nn3, $nn2, $nn1, $g2),
            6 => self::$df::quad($nn2, $nn2, $nn1),
            7 => self::$df::quad($nn3, $nn3, $nn1),
        ];
        foreach ($q as $n => $i) {
            $this->assertTrue($i->equals($i));
            $this->assertInstanceOf(\rdfInterface\Quad::class, $i);
            $this->assertFalse($i->equals($nn1));
            if ($n < 6) {
                $this->assertTrue($nn3->equals($i->getSubject()));
                $this->assertTrue($nn2->equals($i->getPredicate()));
            }
        }
        for ($i = 0; $i <= 6; $i++) {
            for ($j = $i + 1; $j <= 7; $j++) {
                if ($i === 0 && $j === 4 || $i === 1 && $j === 3) {
                    $this->assertTrue($q[$i]->equals($q[$j]), "equals() between Quads $i and $j failed");
                    $this->assertTrue($q[$j]->equals($q[$i]), "equals() between Quads $j and $i failed");
                } else {
                    $this->assertFalse($q[$i]->equals($q[$j]), "equals() between Quads $i and $j failed");
                    $this->assertFalse($q[$j]->equals($q[$i]), "equals() between Quads $j and $i failed");
                }
            }
        }
    }

    public function testQuadExceptions(): void {
        $nn = self::$df::namedNode('baz');
        $l  = self::$df::literal('foo');
        try {
            self::$df::quad($l, $nn, $nn);
            $this->assertTrue(false);
        } catch (BadMethodCallException $ex) {
            $this->assertTrue(true);
        }

        $q = self::$df::quad($nn, $nn, $nn);
        try {
            $q->getValue();
            $this->assertTrue(false);
        } catch (BadMethodCallException $ex) {
            $this->assertTrue(true);
        }
    }

    public function testQuadWith(): void {
        $nn1 = self::$df::namedNode('foo');
        $nn2 = self::$df::namedNode('bar');
        $l1  = self::$df::literal('foo');
        $q1  = self::$df::quad($nn1, $nn1, $nn1);

        $q2 = $q1->withSubject($nn2);
        $this->assertFalse($q1->equals($q2));
        $this->assertTrue($nn2->equals($q2->getSubject()));

        $q3 = $q2->withPredicate($nn2);
        $this->assertFalse($q3->equals($q2));
        $this->assertTrue($nn2->equals($q3->getPredicate()));

        $q4 = $q3->withObject($l1);
        $this->assertFalse($q3->equals($q4));
        $this->assertTrue($l1->equals($q4->getObject()));

        $q5 = $q4->withGraph($nn1);
        $this->assertFalse($q5->equals($q4));
        $this->assertTrue($nn1->equals($q5->getGraph()));

        $this->assertTrue($nn1->equals($q1->getSubject()));
        $this->assertTrue($nn1->equals($q2->getPredicate()));
        $this->assertTrue($nn1->equals($q3->getObject()));
    }

    public function testForeignQuad(): void {
        $bn  = self::$df::blankNode('_:n1');
        $nn  = self::$df::namedNode('foo');
        $l   = self::$df::literal('1', null, RDF::XSD_INT);
        $fbn = self::$fdf::blankNode('_:n1');
        $fnn = self::$fdf::namedNode('foo');
        $fl  = self::$fdf::literal(1, '', RDF::XSD_INT);

        $q  = self::$df::quad($bn, $nn, $l, $nn);
        $fq = self::$fdf::quad($fbn, $fnn, $fl, $fnn);
        $this->assertTrue($q->equals($fq));
        $this->assertFalse($q->equals($fnn));
    }
}
