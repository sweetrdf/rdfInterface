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
interface Parser
{
    public function __construct();

    public function parse(string $input): QuadIterator;

    /**
     *
     * @param  resource $input
     * @return \rdfInterface\QuadIterator
     */
    public function parseStream($input): QuadIterator;
}
