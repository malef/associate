<?php

namespace Malef\Associate\DoctrineOrm\Loader\LoadingStrategy;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use Malef\Associate\DoctrineOrm\Loader\ChunkingStrategy\ChunkingStrategy;
use Malef\Associate\DoctrineOrm\Metadata\AssociationMetadataAdapter;
use Symfony\Component\PropertyAccess\PropertyAccess;

class ToManyAssociationLoadingStrategy implements AssociationLoadingStrategyInterface
{
    /**
     * @var \Malef\Associate\DoctrineOrm\Loader\ChunkingStrategy\ChunkingStrategy
     */
    protected $chunkingStrategy;

    public function __construct(ChunkingStrategy $chunkingStrategy)
    {
        $this->chunkingStrategy = $chunkingStrategy;
    }

    public function supports(AssociationMetadataAdapter $associationMetadataAdapter): bool
    {
        return $associationMetadataAdapter->isToMany();
    }

    public function load(ArrayCollection $sourceEntities, AssociationMetadataAdapter $associationMetadataAdapter): void
    {
        if ($sourceEntities->isEmpty()) {
            return;
        }

        $sourceClassMetadataAdapter = $associationMetadataAdapter->getSourceClassMetadataAdapter();

        // TODO Clean this up. What other situations need to be handled? What about uninitialized proxies here?

        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $filteredSourceIdentities = $sourceEntities->filter(
            function ($sourceEntity) use ($propertyAccessor, $associationMetadataAdapter) {
                $propertyValue = $propertyAccessor->getValue($sourceEntity, $associationMetadataAdapter->getName());

                return $propertyValue instanceof PersistentCollection && !$propertyValue->isInitialized();
            }
        );

        // TODO For initialized collections we should probably check if all items are initialized.

        foreach ($this->chunkingStrategy->chunk($filteredSourceIdentities) as $entityChunk) {
            $queryBuilder = $sourceClassMetadataAdapter->createQueryBuilder('s');
            $queryBuilder
                ->select(
                    sprintf('PARTIAL s.{%s}', $sourceClassMetadataAdapter->getIdentifierFieldName()),
                    't'
                )
                ->leftJoin(
                    sprintf('s.%s', $associationMetadataAdapter->getName()),
                    't'
                )
                ->andWhere(
                    $queryBuilder->expr()->in(
                        's',
                        $sourceClassMetadataAdapter->getIdentifierValueForMany($entityChunk)->getValues()
                    )
                )
                ->getQuery()
                ->execute();
        }
    }
}
