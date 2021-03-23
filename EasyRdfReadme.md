# Intruduction to rdfInterface for EasyRdf users

There are few main differences between the rdfInterface (ecosystem) and EasyRdf from the EasyRdf user's point of view:

* EasyRdf is a single library doing it all. It provides parsers, serializers, implementation of RDF terms (`EasyRdf\Literal` and `EasyRdf\Resource`), RDF dataset (`EasyRdf\Graph`), SPARQL client, etc.
  What's convenient about it is that you install one package and you are ready to go.
  What's bad about it is that all elements are tightly coupled to each other and you can't easily reuse EasyRdf classes outside of the EasyRdf (e.g. use one of EasyRdf parsers without `EasyRdf\Graph`) nor update them selectively (as you would most probably need to update the rest of the EasyRdf as well).
  RdfInterface took a different approach. It's an ecosystem of (rahter small) libraries which can exchange data because all of them use same data structures and follow a common API defined in this repository.
  In the rdfInterface ecosystem you have a separate library for parsing/serializaing ([quickRdfIo](https://github.com/sweetrdf/quickRdfIo)), separate ones implementing RDF terms and dataset (you can choose betwenn [simpleRdf](https://github.com/sweetrdf/simpleRdf) and [quickRdf](https://github.com/sweetrdf/quickRdf)), separate one providing so-called term templates ([termTemplates](https://github.com/sweetrdf/termTemplates), we will talk a lot about term templates later), etc.
  At the beginning it may see much more troubling but installing packages with composer requires only a few key strokes and this approach procides a great flexibility. 
  It allows you to choose between different implementations (like between simpleRdf and quickRdf) and you can pretty easily add you own library to this ecosystem as you can implement only those parts of the rdfInterface you are interested in.
* EasyRdf dataset API is graph node-centric. You get to the node (`EasyRdf\Resource`) you are interested in and then deal with its properties and their values.\
  In contrary to that RdfInterface dataset API is edge-centric. You add/delete/filter/iterate trough edges (quads).
  This might feel strange and first but I will try to convince you it's very convenient.
* EasyRdf is weak-typed. It allows to refer to predicates and nodes using strings with their (shortened or fully-qualified) URIs and when it comes to predicates it even supports a rudimentaty SPARQL paths-like syntax. 
  Unfortunately this flexibility leads to many inconsistencies in the API and inherently leads to errors when non-standard (but still RDF-compliant) URIs are being used. What is worse, this problem can't be solved without introducing serious backward incompatibilities.
  RdfInterface enforces strict typing. Every RDF term has its own class and requires explicit initialization.
  This leads (unfortunately) to more verbose and longer syntax but it allows to avoid ambiguities and allow to benefit from static code analysis of your code (e.g. with [phpstan](https://github.com/phpstan/phpstan))

## Basic tasks

### Parsing data

EasyRdf

```php
$graph = new \EasyRdf\Graph();
$graph->parse('RDF DATA GO HERE');
```

rdfInterface

```php
$parser = new \quickRdfIo\TrigParser(new \quickRdf\DataFactory());
$graph = new \quickRdf\Dataset();
$graph->add($parser->parse('RDF DATA GO HERE'));
```

RdfInterface syntax is longer and more verbose but it decouples the parser and the dataset (meaning the dataset implementation doesn't have to know anything about the parser) making it easy to use any parser we want.

### Finding graph node(s)

EasyRdf

```php
# By URI
$resource = $graph->resource('URI');

# Having a given predicate
$resources = $graph->resourcesMatching('predicateURI');

# Having predicate pointing to a given node
$resources = [];
foreach ($graph->reversePropertyUris('targetNodeURI') as $property) {
    $resources = array_merge($resources, $graph->resourcesMatching($property, $graph->resource('targetNodeURI')));
}

# Having a given literal value of a given predicate
$resources = $graph->resourcesMatching('predicateURI', new \EasyRdf\Literal('value'));

# Having a literal value of a given predicate with any language tag
$resources = [];
foreach ($graph->resourcesMatching('predicateURI') as $i) {
    foreach ($i->allLiterals('predicateURI') as $j) {
        if (!empty($j->getLang())) {
            $resources[] = $i;
            break;
        }
    }
}
```

rdfInterface
```php
use \quickRdf\DataFactory as DF;
use \termTemplates\QuadTemplate as QT;
use \termTemplates\LiteralTemplate as LT;

# By URI
$resource = $graph->copy(new QT(DF::namedNode('URI')));

# Having a given predicate
$resources = $graph->copy(new QT(null, DF::namedNode('predicateURI')));

# Having predicate pointing to a given node
$resources = $graph->copy(new QT(null, null, DF::namedNode('targetNodeURI')));

# Having a literal value of a given predicate with any language tag
$resources = $graph->copy(new QT(null, DF::namedNode('predicateURI'), new LT(null, LT::ANY, ''));
```

What is nice about the rdfInterface syntax is it's fully orthogonal. No matter for what you search for, you always do it in a same way. And it's always a one-liner.

While the EasyRdf syntax is a little shorted in simplest cases, it becomes pretty comples when more advanced searches are used.

What is more, the [termTemplates](https://github.com/sweetrdf/termTemplates) library provides plenty of other term templates allowing you to flexibly match quads you are interested in
(e.g. matching term values with regular expressions, matching values starting/containing/ending with a given string, values greater or smaller than a give value, etc.).

### Interacting with a single graph node

EasyRdf

```php
$resource = $graph->resource('URI');

# iterate over all values of a given predicate
foreach ($resource->all('predicateURI') as $i) {
    // do something
}

# get any value of a given predicate assigning a default value as a fallback
$value = $resource->get('predicateURI') ?? 'defaultValue';

# changing a given predicate value to a given literal
$resource->delete('predicateURI');
$resource->addLiteral('newValue');
```

rdfInterface

```php
use \quickRdf\DataFactory as DF;
use \termTemplates\QuadTemplate as QT;
use \termTemplates\LiteralTemplate as LT;

# iterate over all values of a given predicate
$resource = $graph->copy(new QT(DF::namedNode('URI')));
foreach ($resource->copy(new QT(null, DF::namedNode('predicateURI'))) as $i) {
    // do something
}

# get any value of a given predicate assigning a default value as a fallback
$value = $resource->copy(new QT(null, DF::namedNode('predicateURI')))->current() ?? 'defaultValue';

# changing a given predicate value to a given literal
$filter = new QT(null, DF::namedNode('predicateURI'));
$resource[$filter] = $resource[$filter]->withObject(DF::literal('newValue'));
// or
$resource->delete(new QT(null, DF::namedNode('predicateURI')));
$resource[] = DF::quad($resource->current()->getSubject(), DF::namedNode('predicateURI'), DF::literal('newValue'));
```

### Executing a SPARQL query and dealing with its results

TODO

## Simple operations which you can't do with EasyRdf...

... but can with rdfInterface.

* Make a copy of a dataset.\
  Suprisingly there is no method for doing that.
  You must either serialize and deserialize the `EasyRdf\Graph` object or use a three-levels loop over resources, their properties and each property values.\
  In rdfInterface it's just `$copy = $dataset->copy()` (and there are some goodies - see the optional `$filter` parameter).
* Perform any set operation on two datasets.\
  If you want a union, difference or intersection of two datasets, you must implement it by hand.
  And as all those operations are on a quad/triple level and EasyRdf has no representation of a quad/triple, implementing it by hand is really troublesome (I'm too lazy to write a code snippet performing dataset intersection as it would require few dozens of lines).\
  In rdfInterface there are just `Dataset::add()`, `Dataset::delete()` and `Dataset::deleteExcept()` for in-place set operations and `Dataset::union()`, `Dataset::copyExcept()`, `Dataset::copy()` and `Dataset::xor()` for immutable set operations.

## Fundamental EasyRdf limitations addressed by the rdfInterface

* EasyRdf data model being limited to triples.\
  EasyRdf can only handle triples and its internal architecture makes it really difficult to adjust it in any way.
  There is no hope for quads, there is no hope for RDFstar.\
  RdfInterface natively supports quads and can be easily extended to RDFstar (in fact simpleRdf and quickRdf can handle quads having quads as subjects and/or objects already just I'm not aware of any PHP RDF parser able to parse/serialize RDFstar).

