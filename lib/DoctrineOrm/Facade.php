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
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;

class Facade
{
    const CHUNK_SIZE_FOR_UNINITIALIZED_PROXIES = 'chunk_size_for_uninitialized_proxies';
    const CHUNK_SIZE_FOR_TO_MANY_ASSOCIATIONS = 'chunk_size_for_to_many_associations';

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var array
     */
    protected $options;

    public function __construct(EntityManagerInterface $entityManager, array $options = [])
    {
        $this->entityManager = $entityManager;
        $this->options = $this->resolveOptions($options);
    }

    public function createAssociationTreeBuilder(): AssociationTreeBuilder
    {
        return new AssociationTreeBuilder();
    }

    public function createEntityLoader(): EntityLoader
    {
        $associationCollector = new AssociationCollector(
            PropertyAccess::createPropertyAccessor()
        );

        $uninitializedProxiesLoader = new UninitializedProxiesLoader(
            new ChunkingStrategy($this->options[self::CHUNK_SIZE_FOR_UNINITIALIZED_PROXIES])
        );

        $entityLoaderFactory = new EntityLoaderFactory(
            new AssociationLoader(
                new OneToOneInverseSideAssociationLoadingStrategy(),
                new ToOneAssociationLoadingStrategy(
                    $associationCollector,
                    $uninitializedProxiesLoader
                ),
                new ToManyAssociationLoadingStrategy(
                    new ChunkingStrategy($this->options[self::CHUNK_SIZE_FOR_TO_MANY_ASSOCIATIONS])
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

    protected function resolveOptions(array $options): array
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver
            ->setRequired([
                self::CHUNK_SIZE_FOR_UNINITIALIZED_PROXIES,
                self::CHUNK_SIZE_FOR_TO_MANY_ASSOCIATIONS,
            ])
            ->setAllowedTypes(self::CHUNK_SIZE_FOR_UNINITIALIZED_PROXIES, 'int')
            ->setDefault(self::CHUNK_SIZE_FOR_UNINITIALIZED_PROXIES, 100)
            ->setAllowedTypes(self::CHUNK_SIZE_FOR_TO_MANY_ASSOCIATIONS, 'int')
            ->setDefault(self::CHUNK_SIZE_FOR_TO_MANY_ASSOCIATIONS, 100);

        return $optionsResolver->resolve($options);
    }
}
