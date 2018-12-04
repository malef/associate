<?php

namespace Malef\Associate\DoctrineOrm;

use Malef\Associate\DoctrineOrm\Association\AssociationTreeBuilder;
use Malef\Associate\DoctrineOrm\Collector\AssociationCollector;
use Malef\Associate\DoctrineOrm\Loader\AssociationLoader;
use Malef\Associate\DoctrineOrm\Loader\ChunkingStrategy\ChunkingStrategy;
use Malef\Associate\DoctrineOrm\Loader\DeferredEntityLoaderFactory;
use Malef\Associate\DoctrineOrm\Loader\LoadingStrategy\OneToOneInverseSideAssociationLoadingStrategy;
use Malef\Associate\DoctrineOrm\Loader\LoadingStrategy\ToManyAssociationLoadingStrategy;
use Malef\Associate\DoctrineOrm\Loader\LoadingStrategy\ToOneAssociationLoadingStrategy;
use Malef\Associate\DoctrineOrm\Loader\EntityLoader;
use Malef\Associate\DoctrineOrm\Loader\EntityLoaderFactory;
use Malef\Associate\DoctrineOrm\Loader\UninitializedProxiesLoader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class Facade
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createAssociationTreeBuilder(): AssociationTreeBuilder
    {
        return new AssociationTreeBuilder();
    }

    public function createEntityLoader(): EntityLoader
    {
        $chunkingStrategy = new ChunkingStrategy();

        $associationCollector = new AssociationCollector(
            PropertyAccess::createPropertyAccessor()
        );

        $uninitializedProxiesLoader = new UninitializedProxiesLoader(
            $chunkingStrategy
        );

        $entityLoaderFactory = new EntityLoaderFactory(
            new AssociationLoader(
                new OneToOneInverseSideAssociationLoadingStrategy(),
                new ToOneAssociationLoadingStrategy(
                    $associationCollector,
                    $uninitializedProxiesLoader
                ),
                new ToManyAssociationLoadingStrategy(
                    $chunkingStrategy
                )
            ),
            $associationCollector,
            $uninitializedProxiesLoader
        );

        return $entityLoaderFactory->create($this->entityManager);
    }

    public function createDeferredEntityLoaderFactory(): DeferredEntityLoaderFactory
    {
        return new DeferredEntityLoaderFactory(
            $this->createEntityLoader()
        );
    }
}
