<?php

namespace Malef\Associate\DoctrineOrm\Collector;

use Doctrine\Common\Collections\ArrayCollection;
use Malef\Associate\DoctrineOrm\Metadata\AssociationMetadataAdapter;
use Malef\Associate\DoctrineOrm\Source\UniqueEntitySet;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class AssociationCollector
{
    /**
     * @var PropertyAccessor
     */
    protected $propertyAccessor;

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
