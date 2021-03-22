<?php

namespace rdfInterface\ReferenceImplementation;

use rdfInterface\DefaultGraph as iDefaultGraph;
use rdfInterface\Term;
use rdfInterface\TYPE_DEFAULT_GRAPH;
use sweetrdf\InMemoryStoreSqlite\NamespaceHelper;

class DefaultGraph implements iDefaultGraph
{
    private ?string $iri;

    public function __construct(?string $iri = null)
    {
        $this->iri = $iri;
    }

    public function __toString(): string
    {
        return $this->getValue();
    }

    public function equals(Term $term): bool
    {
        return $this === $term;
    }

    public function getType(): string
    {
        return TYPE_DEFAULT_GRAPH;
    }

    public function getValue(): string
    {
        return $this->iri ?? TYPE_DEFAULT_GRAPH;
    }
}
