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
use rdfInterface\DatasetCompare;

/**
 * Description of LoggerTest
 *
 * @author zozlak
 */
abstract class DatasetCompareTest extends \PHPUnit\Framework\TestCase {

    abstract public static function getDataFactory(): DataFactory;

    abstract public static function getDataset(): DatasetCompare;

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
