<?php

namespace rdfInterface\ReferenceImplementation;

use BadMethodCallException;
use Exception;
use rdfInterface\BlankNode as iBlankNode;
use rdfInterface\DefaultGraph as iDefaultGraph;
use rdfInterface\Literal as iLiteral;
use rdfInterface\NamedNode as iNamedNode;
use rdfInterface\Quad as iQuad;
use rdfInterface\Term as iTerm;

class Quad implements iQuad
{
    private iTerm $subject;

    private iNamedNode $predicate;

    private iTerm $object;

    private iNamedNode | iBlankNode | iDefaultGraph | null $graphIri;

    public function __construct(
        iTerm $subject,
        iNamedNode $predicate,
        iTerm $object,
        iNamedNode | iBlankNode | iDefaultGraph | null $graphIri = null
    ) {
        if ($subject instanceof iLiteral) {
            throw new BadMethodCallException('Subject must be of type NamedNode or BlankNode');
        }
        $this->subject = $subject;
        $this->predicate = $predicate;
        $this->object = $object;
        $this->graphIri = $graphIri ?? new DefaultGraph();
    }

    public function __toString(): string
    {
        return rtrim("$this->subject $this->predicate $this->object $this->graphIri");
    }

    public function getType(): string
    {
        return \rdfInterface\TYPE_QUAD;
    }

    public function equals(iTerm $term): bool
    {
        if ($term instanceof iQuad) {
            return $this->getSubject()->equals($term->getSubject())
                && $this->getPredicate()->equals($term->getPredicate())
                && $this->getObject()->equals($term->getObject())
                && $this->getGraphIri()->equals($term->getGraphIri());
        } 

        return false;
    }

    public function getValue(): string
    {
        throw new BadMethodCallException();
    }

    public function getSubject(): iTerm
    {
        return $this->subject;
    }

    public function getPredicate(): iNamedNode
    {
        return $this->predicate;
    }

    public function getObject(): iTerm
    {
        return $this->object;
    }

    public function getGraphIri(): iNamedNode | iBlankNode | iDefaultGraph
    {
        return $this->graphIri;
    }

    public static function createFromArray(array $triple, string $graph): iQuad
    {
        /*
         * subject
         */
        if ('uri' == $triple['s_type']) {
            $s = new NamedNode($triple['s']);
        } elseif ('bnode' == $triple['s_type']) {
            $s = new BlankNode($triple['s']);
        } else {
            throw new Exception('Invalid subject type given.');
        }

        // predicate
        $p = new NamedNode($triple['p']);

        /*
         * object
         */
        if ('uri' == $triple['o_type']) {
            $o = new NamedNode($triple['o']);
        } elseif ('bnode' == $triple['o_type']) {
            $o = new BlankNode($triple['o']);
        } elseif ('literal' == $triple['o_type']) {
            $o = new Literal($triple['o'], $triple['o_lang'], $triple['o_datatype']);
        } else {
            throw new Exception('Invalid object type given.');
        }

        $g = !empty($graph) ? new NamedNode($graph) : new DefaultGraph();

        return new self($s, $p, $o, $g);
    }

    public function withSubject(iTerm $subject): iQuad
    {
        throw new Exception('withSubject not implemented yet.');
    }

    public function withPredicate(iNamedNode $predicate): iQuad
    {
        throw new Exception('withPredicate not implemented yet.');
    }

    public function withObject(iTerm $object): iQuad
    {
        throw new Exception('withObject not implemented yet.');
    }

    public function withGraphIri(
        iNamedNode | iBlankNode | iDefaultGraph | null $graphIri
    ): iQuad {
        throw new Exception('withGraphIri not implemented yet.');
    }
}
