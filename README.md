[![Build Status](https://travis-ci.org/malef/associate.svg?branch=master)](https://travis-ci.org/malef/associate)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/malef/associate/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/malef/associate/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/malef/associate/v/stable)](https://packagist.org/packages/malef/associate)
[![Total Downloads](https://poser.pugx.org/malef/associate/downloads)](https://packagist.org/packages/malef/associate)
[![Monthly Downloads](https://poser.pugx.org/malef/associate/d/monthly)](https://packagist.org/packages/malef/associate)
[![License](https://poser.pugx.org/malef/associate/license)](https://packagist.org/packages/malef/associate)

# Table of contents

  * [Introduction](#introduction)
  * [License](#license)
  * [Getting started](#getting-started)
  * [Usage examples](#usage-examples)
    * [Efficiently loading associated entities and solving N+1 queries problem](#efficiently-loading-associated-entities-and-solving-N+1-queries-problem)
    * [Deferring association traversal to load entities in bulk](#deferring-association-traversal-to-load-entities-in-bulk)

# Introduction

This library provides optimizations for entity fetching for Doctrine ORM to address N+1 queries problem. It plays especially nicely with `Deferred` implementation from [`webonyx/graphql-php`](https://packagist.org/packages/webonyx/graphql-php) allowing to significantly reduce number of database queries.

# License

This bundle is under the MIT license. See the complete license in `LICENSE` file.

# Getting started

Include this bundle in your project using Composer as follows (assuming it is installed globally):

```bash
$ composer require malef/associate
```

For more information on Composer see its [Introduction](https://getcomposer.org/doc/00-intro.md).

To get the instance of `EntityLoader` for your entity manager you can use the facade provided with the library. For more complex cases you will also need instances of `AssociationTreeBuilder` which can be instantiated with `new` or using the facade.

```php
use Malef\Associate\DoctrineOrm\Facade;
use Malef\Associate\DoctrineOrm\Association\AssociationTreeBuilder;

$facade = new Facade($entityManager);

$entityLoader = $facade->createEntityLoader();

$associationTreeBuilder1 = $facade->createAssociationTreeBuilder();
$associationTreeBuilder2 = new AssociationTreeBuilder();
```

You can also use these classes for defining services appropriate for DI container your framework of choice uses.

That's all - now you're ready to go!

# Usage examples

## Efficiently loading associated entities and solving N+1 queries problem

### Rationale

Let's assume that we're building an e-commerce website using [doctrine/orm](https://packagist.org/packages/doctrine/orm) for persistence. One of the problems we'll run into is N+1 queries problem. It occurs when we fetch some entities from database and then attempt to traverse their associations via getters (e.g. during their serialization).

To give an example, we may have some products that we need to list. Each of them has few variants that we also need to display. If we simply provide this set of products to our template (or serializer, if we're providing some API) then variants for each product will be fetched separately when we try to access the corresponding `PersistentCollection` managed by Doctrine ORM for the first time. While this will work fine it will incur one `SELECT` query for each `Product` instance provided. Hence if we want to list 100 products this way we will end up with 101 database queries being executed, and this number will increase further if we need to follow more relationships.

Some ORMs are addressing this problem for some basic cases. For instance in Eloquent ORM you can use [Lazy Eager Loading](https://laravel.com/docs/5.7/eloquent-relationships#lazy-eager-loading). It is still limited to traversing only one relationship at a time though. Sadly, Doctrine ORM doesn't provide a similar helper.

You can find out more about this problem at [5 Doctrine ORM Performance Traps You Should Avoid](https://tideways.io/profiler/blog/5-doctrine-orm-performance-traps-you-should-avoid) written by [Benjamin Eberlei](https://github.com/beberlei) - see section titled in section *Lazy-Loading and N+1 Queries*. Four ways to address this problem are pointed out there.

Eager loading (solution 3) can be the simplest way to go but in many cases we will find it too rigid. The problem is that it will load the related entities every time and often we need to access then just in few specific cases.

Other solutions are more flexible, like using dedicated DQL query (solution 1) or triggering eager loading of entities after collecting their identifiers (solution 2). These solutions would however result in clunky code and they have to be adjusted depending on whether given association is of *-to-one* or *-to-many* type and whether entities that are already initialized are on the inverse or the owning side of the association. Additionally we sometimes need to join other entities for filtering purposes and we cannot simply fetch everything that is needed in a single query as the result set that needs to be hydrated by Doctrine ORM would become to large. Finally some minor optimizations can be applied if some `\Doctrine\Common\Persistence\Proxy` instances or `\Doctrine\ORM\PersistentCollection` instances are already initialized and hence can be skipped.

This library tries to implement solutions 1 and 2 but in a clean and encapsulated manner that is easy to use in multiple scenarios.

### Basic usage

In the example above it would be only required to precede previously given code with:

```php
use Malef\Associate\DoctrineOrm\Facade;

$facade = new Facade($entityManager);

$entityLoader = $facade->createEntityLoader();
$entityLoader->load($products, 'variants', Product::class);
```

After executing this snippet all variants for given products will be loaded with a single `SELECT` query and calling `getVariants` will not result in any additional queries.

### Possible input arguments

`Malef\Associate\DoctrineOrm\Loader\EntityLoader::load` method accepts following arguments:

* `$entities` - an instance of `iterable` containing root entities that loader should load associations for; it can be for example a plain `array` or Doctrine's `Collection`;

* `$associations` - a `string` containing dot-separated names of one or more relationships to follow in sequence (e.g. `'profile'`, `'order.item.product.variant'`); or an `array` containing one or more relationship names (e.g. `['profile']`, `['order', 'item', 'product', 'variant']`); or an instance of `Malef\Associate\DoctrineOrm\Association\AssociationTree` (see next section for how to use it); only the last case will support branching out in multiple directions when following relationships;

* `$entityClass` - optional; a `string` containing class name for entities included in first array; if not given then entity loader will try to detect it automatically; all entities need to share a single entity class (or a superclass in case of using Doctrine's inheritance functionality);

Input arguments for methods analogous to `EntityLoader::load` (like `Malef\Associate\DoctrineOrm\Loader\DeferredEntityLoader::createDeferred` and `Malef\Associate\DoctrineOrm\Loader\DeferredEntityLoaderFactory::create`) accept similar arguments.

### Loading over multiple relationships

If using dot-separated string, or an array of strings as `$associations` argument it is possible to load entities following multiple associations in sequence. Assuming we have a `Product` entity with many `Variant`s, and these in turn have multiple `Offer`s available, we can use following values as `$associations`:

* `'variants.offers'`,

* `['variants', 'offers']`,

* instance of `Malef\Associate\DoctrineOrm\Association\AssociationTree` built as follows:
  ```php
  $associationTree = $associationTreeBuilder
      ->associate('variants')
      ->associate('offers')
      ->create();
  ```

This will allow us later to use methods like `$product->getVariants()` or `$product->getVariants()->getOffers()` without incurring any additional queries.

If we want to follow multiple multiple associations that are not sequential (i.e. they diverge somehov into multiple paths) our only option is to use `Malef\Associate\DoctrineOrm\Association\AssociationTree`. Assuming that for `Offer` entity we have one `Seller` and multiple `Bidder`s we could use following code:

```php
$associationTree = $associationTreeBuilder
    ->associate('variants')
    ->associate('offers')
    ->diverge()
        ->associate('seller')
    ->endDiverge()
    ->diverge()
        ->associate('offers')
    ->endDiverge()
    ->create();
```

This way we could also call `$product->getVariants()->getOffers()->getSeller()` and `$product->getVariants()->getOffers()->getBidders()` without incurring any additional queries.

### Chunking

If the number of products or associated entities is high then they'll be split in chunks and associations for each chunk will be loaded separately. Chunk size is set by default to `1000` but you are free to alter it, or set it to `null` to disable chunking.

### Limitations

**Important!** It's not possible to reduce the number of queries for one-to-one associations when starting from inverse side - Doctrine ORM loads them by default issuing a separate `SELECT` for each entity. To address this case you may consider changing such association to one-to-many (and use this library afterwards) or using embeddable if possible (in which case embedded entities will be loaded with the same query that loads entities that contain them).

## Deferring association traversal to load entities in bulk

If you're working on a project using Doctrine ORM and providing GraphQL API then this library can play nicely with `Deferred` class provided by [webonyx/graphql-php](https://packagist.org/packages/webonyx/graphql-php). You can read more about the general idea behind this approach at [Solving N+1 Problem](http://webonyx.github.io/graphql-php/data-fetching/#solving-n1-problem) section of its documentation.

Let's assume we need to implement `resolve` function that will return `Variant` instances for `Product` instance. Basic implementation could look as follows:

```php
$resolve = function(Product $product) {
    return $product->getVariants()->getValues();
};
```

But using this approach we would again end up with N+1 queries executed against our database. To alleviate this problem and to load these objects efficiently we can use instance of `DeferredEntityLoader` like this:

```php
use Malef\Associate\DoctrineOrm\Facade;

$facade = new Facade($entityManager);

$deferredEntityLoader = $facade
    ->createDeferredEntityLoaderFactory()
    ->create('variants', Product::class);

$resolve = function(Product $product) use ($deferredEntityLoader) {
    return $deferredEntityLoader->createDeferred(
        [$product],
        function() use ($product) {
            return $product->getVariants()->getValues();
        }
    );
};
```

Et voilà! What `DeferredEntityLoader` will do here is it will accumulate all entities while GraphQL query result is build width first. When GraphQL library attempts to resolve `Deferred` that was returned in our `resolve` function the collector will use `EntityLoader` to load all entities as efficiently as possible based on association tree provided before.
