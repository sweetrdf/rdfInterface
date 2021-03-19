<?php

/*
 * The MIT License
 *
 * Copyright 2021 zozlak.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace rdfInterface;

use BadMethodCallException;
use Stringable;
use zozlak\RdfConstants as RDF;

/**
 *
 * @author zozlak
 */
interface Literal extends Term {

    public const CAST_LEXICAL_FORM = 1;
    public const CAST_NONE         = 2;
    public const CAST_DATATYPE     = 3;

    /**
     * Creates a new literal.
     * 
     * While the created literal must have valid combination of datatype and lang tag
     * (meaning the datatype is rdf:langString if and only if the literal has 
     * a lang tag), it's up to the implementation how to assure it. Both throwing
     * an exception and one of $lang/$datatype parameters taking precedense over 
     * the other are valid solutions.
     * 
     * See https://www.w3.org/TR/rdf11-concepts/#section-Graph-Literal for 
     * a reference.
     * 
     * @param int|float|string|bool|Stringable $value Literal's value.
     *   Only values which can be casted to a string are allowed so it's clear
     *   how to obtain literal's value lexical form (see the RDF specification
     *   mentioned above).
     * @param string|null $lang Literal's lang tag. If null or empty string, the literal
     *   is assumed not to have a lang tag (as an empty lang tag is not allowed in RDF).
     * @param string|null $datatype Literal's datatype. If it's null, the datatype must be
     *   assigned according to the $lang parameter value. Literals with a lang
     *   tag are of type rdf:langString while other literals are assumed to be
     *   of type xsd:string.
     */
    public function __construct(
        int | float | string | bool | Stringable $value, ?string $lang = null,
        ?string $datatype = RDF::XSD_STRING
    );

    /**
     * Returns literal's value.
     * 
     * Separate cast options are needed as the RDF specification defines a few
     * kinds of literal values. See 
     * https://www.w3.org/TR/rdf11-concepts/#section-Graph-Literal for details.
     * 
     * @param int $cast Determines the kind of value being returned:
     *   * \rdfInterface\CAST_LEXICAL_FORM - a string with literal's lexical form.
     *     All implementations must handle this kind of cast.
     *   * \rdfInterface\CAST_NONE - just a value passed to the literal
     *     constructor is returned. All implemenations must handle this kind of 
     *     cast.
     *   * \rdfInterface\CAST_DATATYPE - value mapped to the datatype's domain.
     *     Implementations may handle this kind of cast. It's up to the 
     *     implementation which datatypes are supported and how the mapping is
     *     being done.
     * @return mixed
     */
    public function getValue(int $cast = self::CAST_LEXICAL_FORM): mixed;

    /**
     * Returns literal's language tag.
     * 
     * If a literal lacks a language tag, null should be returned. It means this
     * method can't return an empty string.
     * @return string|null
     */
    public function getLang(): ?string;

    /**
     * Returns literal's datatype.
     * 
     * The method must return the actual datatype even if it's implicit. It
     * means `http://www.w3.org/1999/02/22-rdf-syntax-ns#langString` must be
     * returned for literals with a lang tag and 
     * `http://www.w3.org/2001/XMLSchema#string` must be returned for literals
     * without lang tag and without datatype specified explicitely.
     * 
     * @return string
     * @see __construct()
     */
    public function getDatatype(): string;

    public function withValue(int | float | string | bool | Stringable $value): Literal;

    /**
     * Returns a new literal being a copy of this one with a lang tag set to 
     * a given value.
     * 
     * Be aware setting a lang tag on a literal without one as well as dropping
     * a lang tag from a literal having it implicitly changes literal's datatype.
     * Setting a lang on a literal without one enforces setting its datatype to
     * rdf:langString while dropping a lang from a literal having it changes
     * literal's datatype to xsd:string (see 
     * https://www.w3.org/TR/rdf11-concepts/#section-Graph-Literal for details).
     * @param string|null $lang 
     * @return Literal
     */
    public function withLang(?string $lang): Literal;

    /**
     * Returns a new literal being a copy with this one with datatype set to
     * a given value.
     * 
     * Be aware it's impossibe to set the datatype to rdf::langString that way
     * as it would require setting a non-empty lang tag. The withLang()
     * method should be used in such a case as it changes the datatype implicitly.
     * 
     * As this method by definition doesn't allow to set a datatype allowing
     * a literal to have a lang tag, it must set returned literal's lang tag
     * to null.
     * 
     * This method must throw a \BadMethodCallException when called with a wrong
     * datatype (`http://www.w3.org/1999/02/22-rdf-syntax-ns#langString` or an
     * empty one).
     * 
     * @param string $datatype
     * @return Literal
     * @see withLang()
     * @throws BadMethodCallException
     */
    public function withDatatype(string $datatype): Literal;
}
