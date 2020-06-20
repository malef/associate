<?php

namespace Malef\Associate\DoctrineOrm\Loader\ArgumentConverter;

use Doctrine\Common\Collections\ArrayCollection;
use Malef\Associate\DoctrineOrm\Metadata\MetadataAdapterProvider;
use Malef\Associate\DoctrineOrm\Source\EntitySource;
use Malef\Associate\DoctrineOrm\Source\UniqueEntitySet;

class EntitiesArgumentConverter
{
    /**
     * @throws \Exception
     */
    public function convertToEntitiesSource(
        iterable $entities,
        ?string $entityClass,
        MetadataAdapterProvider $metadataAdapterProvider
    ): EntitySource {
        $entities = $this->convertEntitiesToArrayCollection($entities);

        if ($entities->isEmpty()) {
            return new EntitySource($entities);
        }

        if (!$entityClass) {
            $entityClass = $metadataAdapterProvider->getClassNameForEntities($entities);
        }
        $entityClassMetadataAdapter = $metadataAdapterProvider->getClassMetadataAdapterByClassName($entityClass);

        $entitySource = new EntitySource($entities, $entityClass);
        $entitySource->setClassMetadataAdapter($entityClassMetadataAdapter);

        return $entitySource;
    }

    public function convertEntitiesToArrayCollection(iterable $entities): ArrayCollection
    {
        if (!is_array($entities)) {
            $rootEntities = new UniqueEntitySet();
            foreach ($entities as $entity) {
                $rootEntities->addOne($entity);
            }

            return $rootEntities->getAll();
        }

        return new ArrayCollection($entities);
    }
}
