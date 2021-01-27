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
 * Description of RdfTerm
 *
 * @author zozlak
 */
interface Term
{
    public function __toString(): string;

    public function getType(): string;

    public function getValue(): int | float | string | bool | Stringable;

    public function equals(Term $term): bool;
}
