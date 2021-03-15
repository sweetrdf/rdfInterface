<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace rdfInterface\tests;

use rdfInterface\DataFactory;
use rdfInterface\Literal;
use rdfInterface\Quad;
use rdfInterface\DatasetMapReduce;

/**
 * Description of LoggerTest
 *
 * @author zozlak
 */
abstract class DatasetMapReduceTest extends \PHPUnit\Framework\TestCase {

    abstract public static function getDataFactory(): DataFactory;

    abstract public static function getDataset(): DatasetMapReduce;

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

    public function testMap(): void {
        $d1   = static::getDataset();
        $d1[] = self::$df::quad(self::$df::namedNode('foo'), self::$df::namedNode('baz'), self::$df::literal(1));
        $d1[] = self::$df::quad(self::$df::namedNode('bar'), self::$df::namedNode('baz'), self::$df::literal(5));
        $d2   = $d1->map(function (Quad $x) {
            $obj = $x->getObject();
            return $obj instanceof Literal ? $x->withObject($obj->withValue((float) (string) $obj->getValue() * 2)) : $x;
        });
        $this->assertCount(2, $d1);
        $this->assertEquals(1, (int) (string) $d1[self::$df::quadTemplate(self::$df::namedNode('foo'))]->getObject()->getValue());
        $this->assertEquals(5, (int) (string) $d1[self::$df::quadTemplate(self::$df::namedNode('bar'))]->getObject()->getValue());
        $this->assertCount(2, $d2);
        $this->assertEquals(2, (int) (string) $d2[self::$df::quadTemplate(self::$df::namedNode('foo'))]->getObject()->getValue());
        $this->assertEquals(10, (int) (string) $d2[self::$df::quadTemplate(self::$df::namedNode('bar'))]->getObject()->getValue());
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
        $this->assertEquals(1, (int) (string) $d1[self::$df::quadTemplate(self::$df::namedNode('foo'))]->getObject()->getValue());
        $this->assertEquals(5, (int) (string) $d1[self::$df::quadTemplate(self::$df::namedNode('bar'))]->getObject()->getValue());
    }
}
