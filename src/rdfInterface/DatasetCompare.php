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
 * @author zozlak
 */
interface DatasetCompare extends Dataset
{
    public function every(Quad | QuadTemplate | callable $filter): bool;

    public function none(Quad | QuadTemplate | QuadIterator | callable $filter): bool;

    public function any(Quad | QuadTemplate | QuadIterator | callable $filter): bool;
}
