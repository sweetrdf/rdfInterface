<?php

namespace rdfInterface\ReferenceImplementation;

use Exception;
use rdfInterface\Literal as iLiteral;
use rdfInterface\Term;
use Stringable;

class Literal implements iLiteral
{
    private string $value;

    private ?string $lang;

    private ?string $datatype;

    public function __construct(
        string | Stringable $value,
        ?string $lang = null,
        ?string $datatype = null
    ) {
        $this->value = $value;

        /*
         * @see https://www.w3.org/TR/rdf11-concepts/#section-Graph-Literal
         */
        if (!empty($lang)) {
            $this->lang = $lang;
            $this->datatype = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#langString';
        } else {
            $this->lang = null;
            $this->datatype = $datatype ?? 'http://www.w3.org/2001/XMLSchema#string';
        }
    }

    public function __toString(): string
    {
        $langtype = '';
        if (!empty($this->lang)) {
            $langtype = '@'.$this->lang;
        } elseif (!empty($this->datatype)) {
            $langtype = "^^<$this->datatype>";
        }

        return '"'.$this->value.'"'.$langtype;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getLang(): ?string
    {
        return $this->lang;
    }

    public function getDatatype(): string
    {
        return $this->datatype;
    }

    public function getType(): string
    {
        return \rdfInterface\TYPE_LITERAL;
    }

    public function equals(Term $term): bool
    {
        return $this == $term;
    }

    public function withValue(string | Stringable $value): self
    {
        throw new Exception('withValue not implemented yet');
    }

    public function withLang(?string $lang): self
    {
        throw new Exception('withLang not implemented yet');
    }

    public function withDatatype(?string $datatype): self
    {
        throw new Exception('withDatatype not implemented yet');
    }
}
