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

use zozlak\RdfConstants as RDF;

/**
 * @author zozlak
 */
interface Literal extends Term
{
    public function __construct(
        int | float | string | bool | Stringable $value,
        ?string $lang = null,
        string $datatype = RDF::XSD_STRING
    );

    public function getValue(): int | float | string | bool | Stringable;

    public function getLang(): ?string;

    public function getDatatype(): string;

    public function withValue(int | float | string | bool | Stringable $value): Literal;

    public function withLang(?string $lang): Literal;

    public function withDatatype(?string $datatype): Literal;
}
