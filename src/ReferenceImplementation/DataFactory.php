<?php

namespace rdfInterface\ReferenceImplementation;

use Exception;
use rdfInterface\BlankNode as iBlankNode;
use rdfInterface\DataFactory as iDataFactory;
use rdfInterface\DefaultGraph as iDefaultGraph;
use rdfInterface\Literal as iLiteral;
use rdfInterface\NamedNode as iNamedNode;
use rdfInterface\Quad as iQuad;
use rdfInterface\QuadTemplate as iQuadTemplate;
use rdfInterface\Term as iTerm;
use rdfInterface\Variable as iVariable;
use Stringable;

class DataFactory implements iDataFactory
{
    public static function blankNode(string | Stringable | null $iri = null): iBlankNode
    {
        return new BlankNode($iri);
    }

    public static function namedNode(string | Stringable $iri): iNamedNode
    {
        return new NamedNode($iri);
    }

    public static function defaultGraph(
        string | Stringable | null $iri = null
    ): iDefaultGraph {
        return new DefaultGraph($iri);
    }

    public static function literal(
        string | Stringable $value,
        string | Stringable | null $lang = null,
        string | Stringable | null $datatype = null
    ): iLiteral {
        return new Literal($value, $lang, $datatype);
    }

    public static function quad(
        iTerm $subject,
        iNamedNode $predicate,
        iTerm $object,
        iNamedNode | iBlankNode | iDefaultGraph | null $graphIri = null
    ): iQuad {
        return new Quad($subject, $predicate, $object, $graphIri);
    }

    public static function quadTemplate(
        iTerm | null $subject = null,
        iNamedNode | null $predicate = null,
        iTerm | null $object = null,
        iNamedNode | iBlankNode | iDefaultGraph | null $graphIri = null
    ): iQuadTemplate {
        throw new Exception('quadTemplate is not implemented yet.');
    }

    public static function variable(string | Stringable $name): iVariable
    {
        throw new Exception('variable is not implemented yet.');
    }
}
