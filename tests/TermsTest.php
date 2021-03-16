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
            $this->assertEquals(\rdfInterface\TYPE_NAMED_NODE, $i->getType());
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
            $this->assertEquals(\rdfInterface\TYPE_BLANK_NODE, $i->getType());
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

    public function testLiteralFactory(): void {
        $l  = [
            0 => self::$df::literal('1'),
            1 => self::$df::literal('1', 'eng'),
            2 => self::$df::literal('1', null, RDF::XSD_STRING),
            3 => self::$df::literal('1', null, RDF::XSD_INT),
            4 => self::$df::literal('1', 'deu'),
            5 => self::$df::literal('1', ''),
        ];
        $nn = self::$df::NamedNode('1');
        foreach ($l as $i) {
            $this->assertEquals('1', (string) $i->getValue());
            $this->assertTrue($i->equals($i));
            $this->assertFalse($i->equals($nn));
            $this->assertIsString((string) $i);
            $this->assertEquals(\rdfInterface\TYPE_LITERAL, $i->getType());
        }

        $this->assertNull($l[0]->getLang());
        $this->assertEquals(RDF::XSD_STRING, $l[0]->getDatatype());
        $this->assertFalse($l[0]->equals($l[1]));
        $this->assertTrue($l[0]->equals($l[2]));
        $this->assertFalse($l[0]->equals($l[3]));
        $this->assertFalse($l[0]->equals($l[4]));
        $this->assertTrue($l[0]->equals($l[5]));

        $this->assertEquals('eng', $l[1]->getLang());
        $this->assertEquals(RDF::XSD_STRING, $l[1]->getDatatype());
        $this->assertFalse($l[1]->equals($l[2]));
        $this->assertFalse($l[1]->equals($l[3]));
        $this->assertFalse($l[1]->equals($l[4]));
        $this->assertFalse($l[1]->equals($l[5]));

        $this->assertNull($l[2]->getLang());
        $this->assertEquals(RDF::XSD_STRING, $l[2]->getDatatype());
        $this->assertFalse($l[2]->equals($l[3]));
        $this->assertFalse($l[2]->equals($l[4]));
        $this->assertTrue($l[2]->equals($l[5]));
        $this->assertTrue($l[2]->equals($l[0]));

        $this->assertNull($l[3]->getLang());
        $this->assertEquals(RDF::XSD_INT, $l[3]->getDatatype());
        $this->assertFalse($l[3]->equals($l[4]));
        $this->assertFalse($l[3]->equals($l[5]));

        $this->assertEquals('deu', $l[4]->getLang());
        $this->assertEquals(RDF::XSD_STRING, $l[4]->getDatatype());
        $this->assertFalse($l[4]->equals($l[5]));

        $this->assertNull($l[5]->getLang());
        $this->assertEquals(RDF::XSD_STRING, $l[5]->getDatatype());
        $this->assertTrue($l[5]->equals($l[0]));
        $this->assertFalse($l[5]->equals($l[1]));
        $this->assertTrue($l[5]->equals($l[2]));
        $this->assertFalse($l[5]->equals($l[3]));
        $this->assertFalse($l[5]->equals($l[4]));
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
        $this->assertEquals(RDF::XSD_STRING, $l2->getDatatype());

        $l3 = $l2->withDatatype(RDF::XSD_INT);
        $this->assertEquals('2', (string) $l3->getValue());
        $this->assertNull($l3->getLang());
        $this->assertEquals(RDF::XSD_INT, $l3->getDatatype());

        $l4 = $l2->withDatatype(RDF::XSD_STRING);
        $this->assertEquals('2', $l4->getValue());
        $this->assertEquals('eng', $l4->getLang());
        $this->assertEquals(RDF::XSD_STRING, $l4->getDatatype());

        $l5 = $l3->withLang('deu');
        $this->assertEquals('2', $l5->getValue());
        $this->assertEquals('deu', $l5->getLang());
        $this->assertEquals(RDF::XSD_STRING, $l5->getDatatype());

        $l6 = $l3->withLang(null);
        $this->assertEquals('2', $l6->getValue());
        $this->assertNull($l6->getLang());
        $this->assertEquals(RDF::XSD_INT, $l6->getDatatype());

        $l7 = $l2->withValue('3');
        $this->assertEquals('3', $l7->getValue());
        $this->assertEquals('eng', $l7->getLang());
        $this->assertEquals(RDF::XSD_STRING, $l7->getDatatype());

        $l8 = $l3->withValue('4');
        $this->assertEquals('4', (string) $l8->getValue());
        $this->assertNull($l8->getLang());
        $this->assertEquals(RDF::XSD_INT, $l8->getDatatype());

        // immutability
        $this->assertEquals('1', $l0->getValue());
        $this->assertNull($l0->getLang());
        $this->assertEquals(RDF::XSD_STRING, $l0->getDatatype());

        $this->assertEquals('2', $l1->getValue());
        $this->assertNull($l1->getLang());
        $this->assertEquals(RDF::XSD_STRING, $l1->getDatatype());

        $this->assertEquals('2', $l2->getValue());
        $this->assertEquals('eng', $l2->getLang());
        $this->assertEquals(RDF::XSD_STRING, $l2->getDatatype());

        $this->assertEquals('2', (string) $l3->getValue());
        $this->assertNull($l3->getLang());
        $this->assertEquals(RDF::XSD_INT, $l3->getDatatype());
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

    public function testQuad(): void {
        $nn1 = self::$df::namedNode('foo');
        $nn2 = self::$df::namedNode('bar');
        $nn3 = self::$df::namedNode('baz');
        $l1  = self::$df::literal('foo');
        $l2  = self::$df::literal('foo', 'eng');
        $l3  = self::$df::literal('foo', '', RDF::XSD_STRING);
        $dg1 = self::$df::defaultGraph();
        $dg2 = self::$df::defaultGraph('foo');

        $q = [
            0 => self::$df::quad($nn3, $nn2, $nn1),
            1 => self::$df::quad($nn3, $nn2, $l1),
            2 => self::$df::quad($nn3, $nn2, $l2),
            3 => self::$df::quad($nn3, $nn2, $l3),
            4 => self::$df::quad($nn3, $nn2, $nn1, $dg1),
            5 => self::$df::quad($nn3, $nn2, $nn1, $dg2),
            6 => self::$df::quad($nn2, $nn2, $nn1),
            7 => self::$df::quad($nn3, $nn3, $nn1),
        ];
        foreach ($q as $n => $i) {
            $this->assertTrue($i->equals($i));
            $this->assertEquals(\rdfInterface\TYPE_QUAD, $i->getType());
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

        $q5 = $q4->withGraphIri($nn1);
        $this->assertFalse($q5->equals($q4));
        $this->assertTrue($nn1->equals($q5->getGraphIri()));

        $this->assertTrue($nn1->equals($q1->getSubject()));
        $this->assertTrue($nn1->equals($q2->getPredicate()));
        $this->assertTrue($nn1->equals($q3->getObject()));
    }

    public function testQuadTemplate(): void {
        $nn1 = self::$df::namedNode('foo');
        $l1  = self::$df::literal('foo');
        $l3  = self::$df::literal('foo', '', RDF::XSD_STRING);
        $dg1 = self::$df::defaultGraph();

        $q = [
            0 => self::$df::quadTemplate($nn1, $nn1, $nn1),
            1 => self::$df::quadTemplate($nn1, $nn1, $nn1, $dg1),
            2 => self::$df::quadTemplate(null, null, $l1),
            3 => self::$df::quadTemplate(null, null, $l3),
            4 => self::$df::quadTemplate(null, $nn1, $nn1),
            5 => self::$df::quadTemplate($nn1, null, $nn1),
            6 => self::$df::quadTemplate($nn1, $nn1, null),
            7 => self::$df::quadTemplate(null, $nn1, $nn1, $dg1),
        ];
        foreach ($q as $n => $i) {
            $this->assertTrue($i->equals($i));
            $this->assertEquals(\rdfInterface\TYPE_QUAD_TMPL, $i->getType());
            $this->assertFalse($i->equals($nn1));
            $this->assertIsString((string) $i);
        }
        for ($i = 0; $i <= 6; $i++) {
            for ($j = $i + 1; $j <= 7; $j++) {
                if ($i === 0 && $j === 1 || $i === 2 && $j === 3 || $i === 4 && $j === 7) {
                    $this->assertTrue($q[$i]->equals($q[$j]), "equals() between QuadTemplates $i and $j failed");
                    $this->assertTrue($q[$j]->equals($q[$i]), "equals() between QuadTemplates $j and $i failed");
                } else {
                    $this->assertFalse($q[$i]->equals($q[$j]), "equals() between QuadTemplates $i and $j failed");
                    $this->assertFalse($q[$j]->equals($q[$i]), "equals() between QuadTemplates $j and $i failed");
                }
            }
        }
    }

    public function testQuadTemplateQuad(): void {
        $bn  = self::$df::blankNode();
        $nn1 = self::$df::namedNode('foo');
        $nn2 = self::$df::namedNode('bar');
        $l1  = self::$df::literal('foo');
        $l2  = self::$df::literal(10, null, RDF::XSD_INT);
        $dg1 = self::$df::defaultGraph();
        $q   = [
            0 => self::$df::quad($nn1, $nn1, $nn1),
            1 => self::$df::quad($nn1, $nn1, $l2),
            2 => self::$df::quad($bn, $nn2, $l1),
            3 => self::$df::quad($bn, $nn2, $nn1, $dg1),
            4 => self::$df::quad($nn1, $nn1, $nn1, $nn2),
        ];

        $qt = [
            0 => ['qt' => self::$df::quadTemplate($nn1), 'matches' => [0, 1, 4]],
            1 => ['qt' => self::$df::quadTemplate(null, $nn2), 'matches' => [2, 3]],
            2 => [
                'qt'      => self::$df::quadTemplate(null, null, $l1),
                'matches' => [2]
            ],
            3 => [
                'qt'      => self::$df::quadTemplate(null, null, null, $dg1),
                'matches' => [0, 1, 2, 3, 4]
            ],
            4 => [
                'qt'      => self::$df::quadTemplate(null, null, null, $nn2),
                'matches' => [4]
            ],
            5 => ['qt' => self::$df::quadTemplate($bn, $nn2), 'matches' => [2, 3]],
            6 => ['qt' => self::$df::quadTemplate($nn2), 'matches' => []],
        ];
        foreach ($qt as $n => $i) {
            foreach ($q as $m => $j) {
                $expected = (int) in_array($m, $i['matches']);
                $this->assertEquals($expected, $i['qt']->equals($j), "equals() between QuadTemplate $n and Quad $m failed");
            }
        }
    }

    public function testQuadTemplateWith(): void {
        $nn1 = self::$df::namedNode('foo');
        $nn2 = self::$df::namedNode('bar');
        $l1  = self::$df::literal('foo');
        $q1  = self::$df::quadTemplate($nn1);

        $q2 = $q1->withSubject($nn2);
        $this->assertFalse($q1->equals($q2));
        $this->assertTrue($nn2->equals($q2->getSubject()));

        $q3 = $q2->withPredicate($nn2);
        $this->assertFalse($q3->equals($q2));
        $this->assertTrue($nn2->equals($q3->getPredicate()));

        $q4 = $q3->withObject($l1);
        $this->assertFalse($q3->equals($q4));
        $this->assertTrue($l1->equals($q4->getObject()));

        $q5 = $q4->withGraphIri($nn1);
        $this->assertFalse($q5->equals($q4));
        $this->assertTrue($nn1->equals($q5->getGraphIri()));

        $this->assertTrue($nn1->equals($q1->getSubject()));
        $this->assertNull($q2->getPredicate());
        $this->assertNull($q3->getObject());
    }

    public function testQuadTemplateExceptions(): void {
        $nn = self::$df::namedNode('baz');
        $l  = self::$df::literal('foo');

        $q = self::$df::quadTemplate($nn);
        try {
            $q->getValue();
            $this->assertTrue(false);
        } catch (BadMethodCallException $ex) {
            $this->assertTrue(true);
        }

        try {
            self::$df::quadTemplate($l);
            $this->assertTrue(false);
        } catch (BadMethodCallException $ex) {
            $this->assertTrue(true);
        }

        try {
            self::$df::quadTemplate(null, null, null, null);
            $this->assertTrue(false);
        } catch (BadMethodCallException $ex) {
            $this->assertTrue(true);
        }
    }
}
