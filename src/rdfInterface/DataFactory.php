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
interface DataFactory
{
    public static function namedNode(string | Stringable $iri): NamedNode;

    public static function blankNode(string | Stringable | null $iri = null): BlankNode;

    public static function literal(
        int | float | string | bool | Stringable $value,
        string | Stringable $lang = null,
        string | Stringable $datatype = null
    ): Literal;

    public static function variable(string | Stringable $name): Variable;

    public static function defaultGraph(string | Stringable | null $iri): DefaultGraph;

    public static function quad(
        Term $subject,
        NamedNode $predicate,
        Term $object,
        NamedNode | BlankNode | null $graph = null
    ): Quad;

    public static function quadTemplate(
        Term | null $subject = null,
        NamedNode | null $predicate = null,
        Term | null $object = null,
        NamedNode | BlankNode | null $graph = null
    ): QuadTemplate;
}
