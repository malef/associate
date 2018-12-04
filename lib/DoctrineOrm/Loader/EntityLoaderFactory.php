<?php

namespace Malef\Associate\DoctrineOrm\Loader;

use Malef\Associate\DoctrineOrm\Collector\AssociationCollector;
use Malef\Associate\DoctrineOrm\Loader\ArgumentConverter\AssociationsArgumentConverter;
use Malef\Associate\DoctrineOrm\Loader\ArgumentConverter\EntitiesArgumentConverter;
use Malef\Associate\DoctrineOrm\Metadata\MetadataAdapterProvider;
use Doctrine\ORM\EntityManagerInterface;

class EntityLoaderFactory
{
    /**
     * @var AssociationLoader
     */
    protected $associationLoader;

    /**
     * @var AssociationCollector
     */
    protected $associationCollector;

    /**
     * @var UninitializedProxiesLoader
     */
    protected $uninitializedProxiesLoader;

    /**
     * @param AssociationLoader          $associationLoader
     * @param AssociationCollector       $associationCollector
     * @param UninitializedProxiesLoader $uninitializedProxiesLoader
     */
    public function __construct(
        AssociationLoader $associationLoader,
        AssociationCollector $associationCollector,
        UninitializedProxiesLoader $uninitializedProxiesLoader
    ) {
        $this->associationLoader = $associationLoader;
        $this->associationCollector = $associationCollector;
        $this->uninitializedProxiesLoader = $uninitializedProxiesLoader;
    }

    /**
     * @return EntityLoader
     */
    public function create(EntityManagerInterface $entityManager): EntityLoader
    {
        return new EntityLoader(
            new EntitiesArgumentConverter(),
            new AssociationsArgumentConverter(),
            new MetadataAdapterProvider($entityManager),
            $this->associationLoader,
            $this->associationCollector,
            $this->uninitializedProxiesLoader
        );
    }
}
