<?php

/*
 * This file is part of the quickrdf/rdfInterfaces package and licensed under
 * the terms of the MIT license.
 *
 * (c) Mateusz Żółtak <zozlak@zozlak.org>
 * (c) Konrad Abicht <hi@inspirito.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace rdfInterface;

/**
 * Immutable edge processing methods
 *
 * @author zozlak
 */
interface DatasetMapReduce
{
    /**
     * @param  callable $fn function applied to every quad with signature `fn(quad, dataset)`
     */
    public function map(callable $fn): Dataset;

    /**
     * @param  callable $fn           Aggregate function with signature `fn(accumulator, quad, dataset)`
     *                                applied on each quad and returns last callback result
     * @param  mixed    $initialValue
     */
    public function reduce(callable $fn, $initialValue = null): mixed;
}
