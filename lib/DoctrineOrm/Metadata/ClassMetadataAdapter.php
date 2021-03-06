<?php

namespace Malef\Associate\DoctrineOrm\Metadata;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\ORM\QueryBuilder;

class ClassMetadataAdapter
{
    /**
     * @var MetadataAdapterProvider
     */
    protected $metadataAdapterProvider;

    /**
     * @var EntityRepository
     */
    protected $repository;

    /**
     * @var ClassMetadata
     */
    protected $classMetadata;

    /**
     * @var string|null
     */
    protected $identifierFieldName;

    /**
     * @var AssociationMetadataAdapter[]
     */
    protected $associationMetadataAdapters = [];

    public function __construct(
        MetadataAdapterProvider $metadataAdapterProvider,
        EntityRepository $repository,
        ClassMetadata $classMetadata
    ) {
        $this->metadataAdapterProvider = $metadataAdapterProvider;
        $this->repository = $repository;
        $this->classMetadata = $classMetadata;
    }

    public function getClassName(): string
    {
        return $this->classMetadata->getName();
    }

    public function getRootClassName(): string
    {
        return $this->classMetadata->rootEntityName;
    }

    public function createQueryBuilder(string $rootAlias): QueryBuilder
    {
        return $this->repository->createQueryBuilder($rootAlias);
    }

    /**
     * @return mixed
     *
     * @throws \Exception
     */
    public function getIdentifierValueForOne(object $object)
    {
        $identifierValues = $this->classMetadata->getIdentifierValues($object);

        return $identifierValues[$this->getIdentifierFieldName()];
    }

    public function getIdentifierValueForMany(ArrayCollection $objects): ArrayCollection
    {
        $identifiers = new ArrayCollection();
        foreach ($objects->getValues() as $object) {
            $identifiers->add($this->getIdentifierValueForOne($object));
        }

        return $identifiers;
    }

    /**
     * @throws MappingException
     */
    public function getAssociationMetadataAdapter(string $associationName): ?AssociationMetadataAdapter
    {
        if (!array_key_exists($associationName, $this->associationMetadataAdapters)) {
            try {
                $associationMapping = $this->classMetadata->getAssociationMapping($associationName);
            } catch (MappingException $e) {
                if (0 !== strpos($e->getMessage(), 'No mapping found for field ')) {
                    throw $e;
                }
                $associationMapping = null;
            }

            $associationMetadataAdapter = is_null($associationMapping)
                ? null
                : new AssociationMetadataAdapter(
                    $this->metadataAdapterProvider,
                    $associationMapping
                );

            $this->associationMetadataAdapters[$associationName] = $associationMetadataAdapter;
        }

        return $this->associationMetadataAdapters[$associationName];
    }

    /**
     * @throws \Exception
     */
    public function getIdentifierFieldName(): string
    {
        if (is_null($this->identifierFieldName)) {
            $identifierFieldNames = $this->classMetadata->getIdentifierFieldNames();

            if (1 !== count($identifierFieldNames)) {
                throw new \Exception('Composite primary keys are not supported.');
            }

            $this->identifierFieldName = reset($identifierFieldNames);
        }

        return $this->identifierFieldName;
    }
}
