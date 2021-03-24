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

### Iterating over all triples matching a given criteria

TODO

### Adding new triples

TODO

### Modyfying given triple values

TODO

### Executing a SPARQL query and dealing with its results

TODO - finish the SparqlClient implementation first

## Simple operations which you can't do with EasyRdf...

... but can with rdfInterface.

* Make a copy of a dataset.\
  Suprisingly there is no method for doing that.
  You must either serialize and deserialize the `EasyRdf\Graph` object or use a three-level loop over resources, their predicates and each predicate values.\
  In rdfInterface it's just `$newCopy = $dataset->copy()`.
* Perform any set operation on two datasets.\
  EasyRdf provides no methods to perform graph union, difference or intersection.
  And implementing these operations using EasyRdf's API is quite troublesome (to be honest I'm to lazy to prepare a snippet).  
  RdfInterface provides `Dataset::add()`, `Dataset::delete()` and `Dataset::deleteExcept()` for in-place set operations and `Dataset::union()`, `Dataset::copyExcept()`, `Dataset::copy()` and `Dataset::xor()` for immutable set operations.

## Fundamental EasyRdf limitations addressed by the rdfInterface

* EasyRdf data model is limited to triples.\
  EasyRdf can only handle triples and its internal architecture makes it really difficult to change it.
  Leaving no hope for quads and no hope for the [RDF-star](https://w3c.github.io/rdf-star/).\
  RdfInterface natively supports quads and can be easily extended to RDF-star (in fact [simpleRdf](https://github.com/sweetrdf/simpleRdf/) and [quickRdf](https://github.com/sweetrdf/quickRdf) can already handle quads having quads as subjects and/or objects, just I'm not aware of any PHP RDF parser able to parse/serialize RDF-star).

