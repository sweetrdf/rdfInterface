<?php

/*
 * This file is part of the quickrdf/rdfInterfaces package and licensed under
 * the terms of the MIT license.
 *
 * (c) Mateusz Żółtak <zozlak@zozlak.org>
 * (c) Konrad Abicht <hi@inspiritozozlak.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace rdfInterface;

/**
 * Main, edge(quad) and Dataset-oriented Dataset API
 *
 * @author  zozlak
 * @extends \ArrayAccess<int|Quad|QuadIterator|callable, Quad>
 */
interface Dataset extends QuadIterator, \ArrayAccess, \Countable
{
    public function __construct();

    public function __toString(): string;

    public function equals(Dataset $other): bool;

    // Immutable set operations

    public function copy(Quad | QuadTemplate | QuadIterator | callable | null $filter = null): Dataset;

    public function copyExcept(Quad | QuadTemplate | QuadIterator | callable | null $filter = null): Dataset;

    public function union(Quad | QuadIterator $other): Dataset;

    public function xor(Quad | QuadIterator $other): Dataset;

    // In-place set operations

    /**
     * Adds set of quads.
     *
     * Use array append syntax to append a single quad.
     *
     * @param  Quad|QuadIterator $quads
     */
    public function add(Quad | QuadIterator $quads): void;

    /**
     * @return DataSet callable(Quad, Dataset)
     */
    public function delete(Quad | QuadTemplate | QuadIterator | callable $filter): Dataset;

    /**
     * @return DataSet callable(Quad, Dataset)
     */
    public function deleteExcept(Quad | QuadTemplate | QuadIterator | callable $filter): Dataset;

    // In-place modification

    /**
     * Iterates through all quads replacing them with a callback result.
     *
     * @param  callable $fn with signature `fn(Quad, Dataset): Quad` runs on each quad
     */
    public function forEach(callable $fn): void;

    // ArrayAccess (with narrower types)

    /**
     * @param  Quad|QuadTemplate|callable $offset
     */
    public function offsetExists($offset): bool;

    /**
     * @param  Quad|QuadTemplate|callable $offset
     */
    public function offsetGet($offset): Quad;

    /**
     * @param  Quad|QuadTemplate|callable $offset
     * @param  Quad                       $value
     */
    public function offsetSet($offset, $value): void;

    /**
     * @param  Quad|QuadTemplate|callable $offset
     */
    public function offsetUnset($offset): void;
}
