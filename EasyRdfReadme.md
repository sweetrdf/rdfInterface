# Intruduction to rdfInterface for EasyRdf users

From EasyRdf user's perspective there are few fundamental differences between the EasyRdf nad the rdfInterface ecosystem:

* EasyRdf is a single library doing it all. It provides parsers, serializers, implementation of RDF terms (`EasyRdf\Literal` and `EasyRdf\Resource`), RDF dataset (`EasyRdf\Graph`), SPARQL client, etc.
  What's convenient about it is that you install one package and you are ready to go. But it also makes it less flexible and difficult to modernize, especially in the long term.  
  RdfInterface took a different approach. It's an ecosystem of (rather small) libraries which can work with each other because they implement a common interfaces defined in this repository.
  In the rdfInterface ecosystem you have a separate library for parsing/serializaing RDF ([quickRdfIo](https://github.com/sweetrdf/quickRdfIo)), separate libraries implementing RDF terms and dataset (you can choose between [simpleRdf](https://github.com/sweetrdf/simpleRdf) and [quickRdf](https://github.com/sweetrdf/quickRdf)), separate one providing so-called term templates ([termTemplates](https://github.com/sweetrdf/termTemplates)), etc. It is very important that it's easy to extend the ecosystem with new libraries implementing new features or implementing already available features in a better way.  
    *  All in all it means with rdfInterface you will need to type `composer require` a few times instead of only once.
* EasyRdf's dataset API is graph node-centric. You fetch the node (`EasyRdf\Resource`) you are interested in from the graph and then deal with node's predicate values (being `EasyRdf\Resource` or `EasyRdf\Literal`). EasyRdf has no data structure representing graph's edge.  
  In the contrary RdfInterface's dataset API is edge-centric. You always add/delete/filter/iterate trough graph edges.  
    * It means the same graph operations are expresses in slightly different way in EasyRdf and rdfInterface.
      The [Basic task](#basic-tasks) section below provides a comparison of some sample task.
    * It's hard to tell that one approach is better than the other. All in all it's quite subjective and comes down to personal taste and habits.
      Anyway I hope you'll see some advantages brought by the approach introduced by the rdfInterface.
* EasyRdf is weak-typed. It allows to refer to predicates and named nodes using strings containing their (shortened or fully-qualified) URIs.
  Literals can be represented by just strings.
  When it comes to predicates it even supports a rudimentaty SPARQL paths-like syntax.  
  RdfInterface doesn't allow that. RdfInterface enforces strict typing - named node/predicate has to be an `rdfInterface\NamedNode` object, literal has to be an `rdfInterface\Literal` object, etc.
    * I'm pretty sure you will find rdfInterface behavior annoying.
      It may feel like introducing a lot of unnecessary boilerplate code.
      While I agree it makes the syntax longer, it's for a reason.
      There are many corner cases where the syntax used by the EasyRdf is intrinsically ambigous.
      Strict typing assures there are no such ambiguities in the rdfInterface API.

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

RdfInterface syntax is longer and more verbose (we explicitly specify three classes instead of only one) but it decouples the parser and the dataset. It means we can freely mix a parser, a terms factory and a dataset implementations (of course until all of them are rdfInterface-compliant).

EasyRdf provides us with a shorter syntax at the cost of limiting us to parsers embedded into the EasyRdf.

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
# Having a literal value of a given predicate with any non-empty language tag
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
# Having a literal value of a given predicate with any non-empty language tag
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

