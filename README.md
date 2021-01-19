# Set of RDF interfaces for PHP

## Why do we need a common interfaces

The PHP RDF ecosystem suffers from big monolythic libraries trying to provide a full RDF stack 
from parsers, trough triplestores, serializers and sometimes even SPARQL engines in a one library.

It makes them quite difficult to maintain and extend.
It also makes it impossible to couple parts of different libraries with each other, 
e.g. combine a faster parser from one library with a nicer triplestore from the other.

The solution for these troubles is to agree on

* A set of separate RDF stack layers: parser, serializer, dataset, SPARQL client, etc.
* Common interfaces each layer should use to communicate with the other
  (think of it as a PSR-7 for RDF).

## Reference solutions

[RDF/JS](http://rdf.js.org/), [RDFLib](https://rdflib.readthedocs.io/en/stable/) as examples of good APIs from other programming languages
as well as [EasyRdf](https://github.com/easyrdf/easyrdf) and [ARC2](https://github.com/semsol/arc2) as a reference of existing PHP solutions.

## Reference implementation

* The reference implementation of various `Term` classes and the `Dataset` class is provided by the [quickRdf](https://github.com/zozlak/quickRdf) library.
* A quick and dirty implementation of a few parsers and serialisers is provided by the [quickRdfIo](https://github.com/zozlak/quickRdfIo) library.
* Some generic helpers which can be reuesed when developing your own implementations or plugging foreign code can be found in the [rdfHelpers](https://github.com/zozlak/rdfHelpers) library.

## Design decisions

### Strong typing

I wanted the API to use classes (instead e.g. arrays with a well known internal structure). 

Using classes provides unambiguity and allowes to leverage static code analysis. And when it comes to errors, it generates errors which are easier to understand.

### Immutability

Following the first design decision brings an important issue. Let's compare two code variants:

```php
class GraphObject {
    (...)
    public function getRandomQuad(): Quad { (...) }
}
class GraphArray {
    (...)
    public function getRandomQuad(): array { (...) }
}
$g1 = new GraphObject();
$g2 = new GraphArray();
(... fill in $g1 and $g2 with some data ...)
$q1 = $g1->getRandomQuad();
$q2 = $g2->getRandomQuad();
$q1['object']['value'] = 'foo';
$q2->object->value = 'foo';
```

In the array implementation we expect the change not to be propagated back to the graph (as arrays in PHP are passed by value by default).
In the object implementation our intuition tells us the change is propagated back to the graph (as objects in PHP >=5 are passed by reference).

In my opinion the lack of propagation behaviour is more intuitive and desired in typical use cases.

There are two ways of achieving it while using classes:

* (deep) Cloning every object before returning/after receiving it by a function.
* By enforcing objects are immutable.
  Meaning one can only get a new copy of the object with a given property being assigned a new value but can't modify a value of already existing object
  (like all `withSOMETHING()` methods of the [PSR-7](https://www.php-fig.org/psr/psr-7/#3-interfaces)).
  In such a case the last line in the code above would look as follows:
  ```php
  $q2->getObject()->withValue('foo');
  ```

The second approach has three benefits.

* It's much easier to implement without flaws. Deep cloning brings performance penalty so we'll try to guess where we can safely avoid it and we're likely to make wrong guesses.
* Modern PHP programmers are already familiar with the idea.
* It allows to easily implement a global objects cache which can save quite some memory in dense graphs
  (if an object is immutable we can just use references to its single copy, no matter in how many copies we use it).

### Use streams for data import/export

Streams are far more flexible than strings.
Allow asynchronous operation, lower memory footprint, coupling with modern PHP HTTP interface, etc.

And a string can be easilly packed as an in memory stream.

Let's just use streams.

### Reuse native PHP interfaces

PHP provides quite some native interfaces which can be useful as parts of the RDF API, e.g.

* [iterable](https://www.php.net/manual/en/language.types.iterable.php) over edges/nodes of a graph or edges of a graph node.
* [ArrayAccess](https://www.php.net/manual/en/class.arrayaccess.php) for adding/removing/accessing edges/nodes of a graph or a graph node.
* [Countable](https://www.php.net/manual/en/class.countable.php) for e.g. counting quads in a graph.

Using native interfaces makes the library easier to learn and feel better integrated.

### Extensibility

There API should be ready for extensions.

RDF is changing with new ideas being introduced (like RDF*) and the API should be able to accomodate such developments.

That's the purpose of the `Term` class and the reason for `Quad`/`QuadTemplate` use `Term` as the type for subject and object.

It doesn't mean particular implementations must support just any `Term`.
Implementations may support any subset they want, they just check types and throw errors when they see something they can't process.

## Design considerations

This should be probably copied to GitHib issues.

### Do we want a node-oriented API or edge-oriented API for a dataset?

EasyRdf has only node-oriented API.
RDF/JS has only edge-oriented API.
RDFLib has both.

I would say we want both.

### QuadIterator vs iterable

QuadIterator allows strong typing but is more troublesome to implement.

A workaround would be to provide a utility class providing a QuadIterator over an array and a Generator.

### Can Quad properties be null?

EasyRdf doesn't really recognize the concept of a triple/quad.

The RDF/JS Quad class doesn't allow for empty components which requires separate Dataset methods for working with Quads and with Quad fragments 
(e.g. `delete(Quad $quad)` and `deleteMatches(?Term $sbj, ?Term $pred, ?Term $obj, ?Term $graph)`).

RDFLib doesn't have a special datatype for a triple/quad and uses a simple three/four element Python tuple to represent them.
This solution allows empty triple/quad parts.

As we prefer for a Quad to be an object (so we can easily assure it's immutable) 
and it seems useful to be able to distinuigh beetween a complete and an incomplete quad
we may try to use two interfaces - one for a complete Quad and the other allowing empty elements.

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

Because of that let's assume for now the Term should provide a `getValue()` method.

### Should Term provide some kind of serialization method?

While it's tempting to incorporate some kind of serialization into the Term interface it isn't a good idea.

The main reason is it breaks separation of the serialization layer.
Also as there are dozens of serialization formats and if we implement one, we will quickly
want to implement another. Before we realize, the serialization layer will be completely
merged with the Term. And we would definitely prefer to separate them.

A most common (e.g. ntriples/nquads) Term-level serialization routines can be provided in a separate utility library.
