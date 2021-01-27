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
interface RdfNamespace
{
    public function add(string $uri, ?string $prefix = null): string;

    public function remove(string $prefix): void;

    public function get(string $prefix): string;

    /**
     * @return array<string>
     */
    public function getAll(): array;

    public function expand(string $shortIri): NamedNode;

    public function shorten(NamedNode $Iri, bool $create): string;
}
