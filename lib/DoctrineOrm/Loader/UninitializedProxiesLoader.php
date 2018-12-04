<?php

namespace Malef\Associate\DoctrineOrm\Loader;

use Malef\Associate\DoctrineOrm\Loader\ChunkingStrategy\ChunkingStrategy;
use Malef\Associate\DoctrineOrm\Metadata\ClassMetadataAdapter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Proxy\Proxy;

class UninitializedProxiesLoader
{
    /**
     * @var ChunkingStrategy
     */
    protected $chunkingStrategy;

    public function __construct(ChunkingStrategy $chunkingStrategy)
    {
        $this->chunkingStrategy = $chunkingStrategy;
    }

    /**
     * @param ArrayCollection      $entities
     * @param ClassMetadataAdapter $classMetadataAdapter
     */
    public function load(
        ArrayCollection $entities,
        ClassMetadataAdapter $classMetadataAdapter
    ) {
        $uninitializedProxies = new ArrayCollection();
        foreach ($entities->getValues() as $entity) {
            if ($entity instanceof Proxy && !$entity->__isInitialized()) {
                $uninitializedProxies->add($entity);
            }
        }

        if ($uninitializedProxies->isEmpty()) {
            return;
        }

        foreach ($this->chunkingStrategy->chunk($uninitializedProxies) as $uninitializedProxiesChunk) {
            $queryBuilder = $classMetadataAdapter->createQueryBuilder('e');

            $queryBuilder
                ->andWhere(
                    $queryBuilder->expr()->in(
                        'e',
                        $classMetadataAdapter->getIdentifierValueForMany($uninitializedProxiesChunk)->getValues()
                    )
                )
                ->getQuery()
                ->execute();
        }
    }
}
