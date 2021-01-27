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
 * Extends Term because of RDF*
 *
 * Remarks:
 * - getValue() should throw an error
 * - null return types in getSubject(), getPredicate() and getObject() make it
 *   possible for QuadTemplate to extend this interface
 *
 * @author zozlak
 */
interface Quad extends Term
{
    /**
     * @param Term                     $subject
     * @param NamedNode                $predicate
     * @param Term                     $object
     * @param NamedNode|BlankNode|null $graphIri
     */
    public function __construct(
        Term $subject,
        NamedNode $predicate,
        Term $object,
        NamedNode | BlankNode | null $graphIri = null
    );

    public function getSubject(): Term;

    public function getPredicate(): NamedNode;

    public function getObject(): Term;

    public function getGraphIri(): NamedNode | BlankNode;

    public function withSubject(Term $subject): Quad;

    public function withPredicate(NamedNode $predicate): Quad;

    public function withObject(Term $object): Quad;

    public function withGraphIri(NamedNode | BlankNode $graphIri): Quad;
}
