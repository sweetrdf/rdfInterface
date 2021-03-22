<?php

namespace rdfInterface\ReferenceImplementation;

use rdfInterface\NamedNode as iNamedNode;
use rdfInterface\Term;

class NamedNode implements iNamedNode
{
    private string $iri;

    public function __construct(string $iri)
    {
        $this->iri = $iri;
    }

    public function __toString(): string
    {
        return '<'.$this->iri.'>';
    }

    public function getValue(): string
    {
        return $this->iri;
    }

    public function getType(): string
    {
        return \rdfInterface\TYPE_NAMED_NODE;
    }

    public function equals(Term $term): bool
    {
        return $this == $term;
    }
}
