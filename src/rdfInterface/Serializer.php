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
interface Serializer
{
    public function __construct();

    public function serialise(QuadIterator $graph, ?RdfNamespace $nmsp = null): string;

    /**
     *
     * @param  resource          $output
     * @param  QuadIterator      $graph
     * @param  RdfNamespace|null $nmsp
     * @return void
     */
    public function serialiseStream(
        $output,
        QuadIterator $graph,
        RdfNamespace | null $nmsp = null
    ): void;
}
