<?php

namespace Malef\Associate\DoctrineOrm\Collector;

use Malef\Associate\DoctrineOrm\Source\UniqueEntitySet;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Malef\Associate\DoctrineOrm\Metadata\AssociationMetadataAdapter;

class AssociationCollector
{
    /**
     * @var PropertyAccessor
     */
    protected $propertyAccessor;

    /**
     * @param PropertyAccessor $propertyAccessor
     */
    public function __construct(PropertyAccessor $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    public function collect(
        ArrayCollection $sourceEntities,
        AssociationMetadataAdapter $associationMetadataAdapter
    ): ArrayCollection {
        return $associationMetadataAdapter->isToMany()
            ? $this->collectToManyAssociation($sourceEntities, $associationMetadataAdapter)
            : $this->collectToOneAssociation($sourceEntities, $associationMetadataAdapter);
    }

    protected function collectToManyAssociation(
        ArrayCollection $sourceEntities,
        AssociationMetadataAdapter $associationMetadataAdapter
    ): ArrayCollection {
        $targetEntities = new UniqueEntitySet();

        foreach ($sourceEntities as $sourceEntity) {
            $targetEntities->addMany(
                $this->propertyAccessor->getValue($sourceEntity, $associationMetadataAdapter->getName())->getValues()
            );
        }

        return $targetEntities->getAll();
    }

    protected function collectToOneAssociation(
        ArrayCollection $sourceEntities,
        AssociationMetadataAdapter $associationMetadataAdapter
    ): ArrayCollection {
        $targetEntities = new UniqueEntitySet();

        foreach ($sourceEntities as $sourceEntity) {
            $targetEntity = $this->propertyAccessor->getValue($sourceEntity, $associationMetadataAdapter->getName());
            if ($targetEntity) {
                $targetEntities->addOne($targetEntity);
            }
        }

        return $targetEntities->getAll();
    }
}
