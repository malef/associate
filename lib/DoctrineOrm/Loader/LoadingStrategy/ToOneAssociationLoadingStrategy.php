<?php

namespace Malef\Associate\DoctrineOrm\Loader\LoadingStrategy;

use Doctrine\Common\Collections\ArrayCollection;
use Malef\Associate\DoctrineOrm\Collector\AssociationCollector;
use Malef\Associate\DoctrineOrm\Loader\UninitializedProxiesLoader;
use Malef\Associate\DoctrineOrm\Metadata\AssociationMetadataAdapter;

class ToOneAssociationLoadingStrategy implements AssociationLoadingStrategyInterface
{
    /**
     * @var AssociationCollector
     */
    protected $associationCollector;

    /**
     * @var UninitializedProxiesLoader
     */
    protected $uninitializedProxiesLoader;

    public function __construct(
        AssociationCollector $associationCollector,
        UninitializedProxiesLoader $uninitializedProxiesLoader
    ) {
        $this->associationCollector = $associationCollector;
        $this->uninitializedProxiesLoader = $uninitializedProxiesLoader;
    }

    public function supports(AssociationMetadataAdapter $associationMetadataAdapter): bool
    {
        return
            $associationMetadataAdapter->isManyToOne()
            || ($associationMetadataAdapter->isOneToOne() && $associationMetadataAdapter->isOwningSide())
        ;
    }

    public function load(ArrayCollection $sourceEntities, AssociationMetadataAdapter $associationMetadataAdapter): void
    {
        $targetEntities = $this->associationCollector->collect(
            $sourceEntities,
            $associationMetadataAdapter
        );

        $this->uninitializedProxiesLoader->load(
            $targetEntities,
            $associationMetadataAdapter->getTargetClassMetadataAdapter()
        );
    }
}
