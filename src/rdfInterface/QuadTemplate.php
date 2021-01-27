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
interface QuadTemplate extends Term
{
    /**
     * At least one parameter has to be not null.
     *
     * @param Term|null                $subject
     * @param NamedNode|null           $predicate
     * @param Term|null                $object
     * @param NamedNode|BlankNode|null $graphIri
     */
    public function __construct(
        Term | null $subject = null,
        NamedNode | null $predicate = null,
        Term | null $object = null,
        NamedNode | BlankNode | null $graphIri = null
    );

    public function getSubject(): Term | null;

    public function getPredicate(): NamedNode | null;

    public function getObject(): Term | null;

    public function getGraphIri(): NamedNode | BlankNode | null;

    public function withSubject(Term | null $subject): QuadTemplate;

    public function withPredicate(NamedNode | null $predicate): QuadTemplate;

    public function withObject(Term | null $object): QuadTemplate;

    public function withGraphIri(NamedNode | BlankNode | null $graphIri): QuadTemplate;
}
