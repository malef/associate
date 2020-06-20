<?php

namespace Malef\Associate\DoctrineOrm\Loader;

use Malef\Associate\DoctrineOrm\Association\AssociationTree;
use Malef\Associate\DoctrineOrm\Loader\ArgumentConverter\AssociationsArgumentConverter;
use Malef\Associate\DoctrineOrm\Loader\ArgumentConverter\EntitiesArgumentConverter;

class DeferredEntityLoaderFactory
{
    /**
     * @var EntityLoader
     */
    protected $entityLoader;

    /**
     * @var AssociationsArgumentConverter
     */
    protected $associationsArgumentConverter;

    public function __construct(EntityLoader $entityLoader)
    {
        $this->entityLoader = $entityLoader;
        $this->associationsArgumentConverter = new AssociationsArgumentConverter();
    }

    /**
     * @param AssociationTree|string[]|string $associationTree
     *
     * @throws \Exception
     */
    public function create(
        $associationTree,
        ?string $entityClassName
    ): DeferredEntityLoader {
        return new DeferredEntityLoader(
            new EntitiesArgumentConverter(),
            $this->entityLoader,
            $this->associationsArgumentConverter->convertToAssociationTree($associationTree),
            $entityClassName
        );
    }
}
