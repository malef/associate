<?php

namespace Malef\Associate\DoctrineOrm\Loader;

use GraphQL\Deferred;
use Malef\Associate\DoctrineOrm\Association\AssociationTree;
use Malef\Associate\DoctrineOrm\Loader\ArgumentConverter\EntitiesArgumentConverter;

class DeferredEntityLoader
{
    /**
     * @var EntityLoader
     */
    protected $entityLoader;

    /**
     * @var EntitiesArgumentConverter
     */
    protected $entitiesArgumentConverter;

    /**
     * @var AssociationTree
     */
    protected $associationTree;

    /**
     * @var string|null
     */
    protected $entityClassName;

    /**
     * @var array
     */
    protected $entities = [];

    public function __construct(
        EntitiesArgumentConverter $entitiesArgumentConverter,
        EntityLoader $entityLoader,
        AssociationTree $associationTree,
        ?string $entityClassName
    ) {
        $this->entitiesArgumentConverter = $entitiesArgumentConverter;
        $this->entityLoader = $entityLoader;
        $this->associationTree = $associationTree;
        $this->entityClassName = $entityClassName;
    }

    public function createDeferred(iterable $entities, \Closure $resolveCallback): Deferred
    {
        foreach ($entities as $entity) {
            $this->entities[] = $entity;
        }

        return new Deferred(
            function () use ($resolveCallback) {
                $this->load();

                return $resolveCallback();
            }
        );
    }

    protected function load(): void
    {
        if (!$this->entities) {
            return;
        }

        $entities = $this->entitiesArgumentConverter->convertEntitiesToArrayCollection($this->entities);

        $this->entityLoader->load(
            $entities,
            $this->associationTree,
            $this->entityClassName
        );

        $this->entities = [];
    }
}
