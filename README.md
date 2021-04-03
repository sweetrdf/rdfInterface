# RDF interfaces for PHP

## Why do we need common interfaces?

The PHP RDF ecosystem suffers from big monolythic libraries trying to provide a full RDF stack 
from parsers to triplestores, serializers and sometimes even SPARQL engines in one library.

It makes them quite difficult to maintain and extend.
It also makes it impossible to couple parts of different libraries with each other, 
e.g. combine a faster parser from one library with a nicer triplestore from another.

The solution for these troubles is to agree on

* A set of separate RDF stack layers: parser, serializer, dataset, SPARQL client, etc.
* Common interfaces each layer should use to communicate with the other (think of it as a PSR-7 for RDF).

## Implementations

* The reference implementation of `Term` and the `Dataset` classes are provided by the [quickRdf](https://github.com/sweetrdf/quickRdf) library and the [simpleRdf](https://github.com/sweetrdf/simpleRdf) library.
* Turtle, NTriples, NQuads and NTriplesStar parsers and serialisers are provided by the [quickRdfIo](https://github.com/sweetrdf/quickRdfIo) library.
* A collection of `QuadTemplate` and `LiteralTemplate` classes providing a convenient way for quads/triples filtering can be found in the [termTemplates](https://github.com/sweetrdf/termTemplates) library.
* The [sparqlClient](https://github.com/sweetrdf/sparqlClient) library provides a SPARQL client (still in early development).
* Generic helpers which can be reuesed when developing your own implementations or plugging foreign code can be found in the [rdfHelpers](https://github.com/sweetrdf/rdfHelpers) library.

## Compliance tests

The [rdfInterfaceTests])https://github.com/sweetrdf/rdfInterfaceTests) provides a set of tests for validating if your library is compliant with the rdfInterface.

## For EasyRdf users

If you are using EasyRdf, you are likely to find the rdfInterface API quite strange and difficult to understand.\
[This document](EasyRdfReadme.md) should help.

## Design decisions

### Reference solutions

[RDF/JS](http://rdf.js.org/), [RDFLib](https://rdflib.readthedocs.io/en/stable/) are examples of good APIs from other programming languages as well as [EasyRdf](https://github.com/easyrdf/easyrdf) and [ARC2](https://github.com/semsol/arc2) as a reference of existing PHP solutions.


### Strong typing

Using classes instead of arrays comes with the following advantages. Classes provide unambiguity and allow to leverage static code analysis. When it comes to errors, it also generates errors which are easier to understand.

### Immutability

Using strong typing brings an important issue. Let's compare two code variants:

```php
class GraphObject {
    (...)
    public function getRandomQuad(): Quad { (...) }
}

class GraphArray {
    (...)
    public function getRandomQuad(): array { (...) }
}

$gObj = new GraphObject();
$gArr = new GraphArray();

$qObj = $gObj->getRandomQuad();
$qObj->object->value = 'foo';

$qArr = $gArr->getRandomQuad();
$qArr['object']['value'] = 'foo';

```

In the array implementation we expect the change not to be propagated back to the graph (as arrays in PHP are passed by value by default).
In the object implementation our intuition tells us the change is propagated back to the graph (as objects in PHP >=5 are passed by reference).

In our opinion the lack of propagation behaviour is more intuitive and desired in typical use cases.

There are two ways of achieving lack of propagation while using classes:

* (deep) Cloning every object before returning/after receiving it by a function.
* Making object immutable.
  Meaning one can only get a new copy of the object with a given property being assigned a new value but can't modify a value of already existing object
  (like all `withSOMETHING()` methods of the [PSR-7](https://www.php-fig.org/psr/psr-7/#3-interfaces)).
  In such a case the last line in the code above would look as follows:
  ```php
  $qObj->getObject()->withValue('foo');
  ```

The second approach has three benefits:

1. It's much easier to implement without flaws. Deep cloning brings performance penalty and quite some boiler plate code.
   This makes it likely for developers to avoid deep cloning and this will often lead to (hard to track) bugs.
2. Modern PHP programmers are already familiar with the idea.
3. It allows a straightforward implementation of a global objects cache, which can save quite some memory in dense graphs
  (if an object is immutable we can just use references to its single copy, no matter how many times it appears in a graph).

### Use streams for data import and export

Streams are far more flexible than strings.
They allow asynchronous operation, they have lower memory footprint, they fit the PSR-7 interface nicely and much more.

Last but not least a string can be easilly packed into a in-memory stream.

### Reuse native PHP interfaces

PHP itself provides useful native interfaces which reuse in the RDF API, e.g.

* [iterable](https://www.php.net/manual/en/language.types.iterable.php) over edges/nodes of a graph.
* [ArrayAccess](https://www.php.net/manual/en/class.arrayaccess.php) for adding/removing/accessing edges of a graph.
* [Countable](https://www.php.net/manual/en/class.countable.php) for e.g. counting quads in a graph.

Using native interfaces makes the library easier to learn and it feels better integrated with the PHP ecosystem.

### Extensibility

Our API must be easy to extend.

RDF is changing with new ideas being introduced (like RDF*) and the API should be able to accomodate such developments.

That's the purpose of the `Term` class and the reason for `Quad`/`QuadTemplate` use `Term` as the type for subject and object.

It doesn't mean particular implementations must support just any `Term`.
Implementations may support any subset they want, they should just check argument types and throw errors when they see something they can't process.

## Design considerations

This should be probably copied to GitHib issues.

### Do we want a node-oriented API or edge-oriented API for a dataset?

EasyRdf has only node-oriented API.
RDF/JS has only edge-oriented API.
RDFLib has both.

Initially I would say we want both.
But the more I'm using it, the more I'm convinced a library providing a ready to reuse set of `QuadTemplate` classes
coupled with the edge-oriented API can easily do the job of the node-oriented API.

### QuadIterator vs iterable

QuadIterator allows strong typing but is more troublesome to implement.

A workaround would be to provide a utility class providing a QuadIterator over an arrays and Generators.

### Can Quad properties be null?

EasyRdf doesn't really recognize the concept of a triple/quad.

The RDF/JS Quad class doesn't allow for empty components which requires separate Dataset methods for working with Quads and with Quad fragments 
(e.g. `delete(Quad $quad)` and `deleteMatches(?Term $sbj, ?Term $pred, ?Term $obj, ?Term $graph)`).

RDFLib doesn't have a special datatype for a triple/quad and uses a simple three/four element Python tuple to represent them.
This solution allows empty triple/quad parts.

As we prefer a Quad to be an object (so we can easily assure it's immutable) 
and it seems useful to be able to distinuigh beetween a complete and an incomplete quad
we may try to use two interfaces - one for a complete Quad and the other allowing empty elements (QuadTemplate).

### Should Quads be aware of Dataset they belong too?

No, they shouldn't.

It would require implementations of the `ParserInterface` to create a `Dataset` which would couple parser with a particular `Dataset` implementation.
We definitely don't want that.

For dataset-aware Quads we would need a separate interface (something like RDFLib's `rdflib.resource`).

### Do we want RDFLib array-like Dataset access API?

I like this API so I would say yes.

It means the Dataset interface should extend the PHP's [ArrayAccess](https://www.php.net/manual/en/class.arrayaccess.php) interface.

### Do we need to distinguish __toString() and getValue()?

While it's tempting to assume `__toString()` can be used as a *get object's value* method, it leads to difficult questions like:

* What should `Literal::__toString()` return? Only the value or maybe also the langtag/datatype.
  Both variants have similar number of advantages and disadvantages and in both cases existence of another method providing the alternative output is desired.
* What about classes which don't have just a value (like the Quad)?
  If for such classes `__toString()` means *just return a string representation which can be nicely printed*,
  we end up with a semantically inconsistent API which would be better to avoid.

Because of that let's assume the Term should provide a separate `getValue()` method.

### Should Term provide some kind of serialization method?

While it's tempting to incorporate some kind of serialization into the Term interface I think it isn't a good idea.

The main reason is it breaks separation of the serialization layer (which would at least partially overlap with Term).

Also, there are dozens of serialization formats and if we implement one, we will quickly
want to implement another. Before we realize, the serialization layer will be completely
merged with the Term. And we would definitely prefer to separate them.

A most common (e.g. ntriples/nquads) Term-level serialization routines can be provided in a separate utility library.
