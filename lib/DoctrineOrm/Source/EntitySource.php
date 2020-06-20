<?php

namespace Malef\Associate\DoctrineOrm\Source;

use Doctrine\Common\Collections\ArrayCollection;
use Malef\Associate\DoctrineOrm\Metadata\ClassMetadataAdapter;

class EntitySource
{
    /**
     * @var ArrayCollection
     */
    protected $entities;

    /**
     * @var string|null
     */
    protected $entityClass;

    /**
     * @var ClassMetadataAdapter|null
     */
    protected $classMetadataAdapter;

    public function __construct(ArrayCollection $entities, ?string $entityClass = null)
    {
        $this->entities = $entities;
        $this->entityClass = $entityClass;
    }

    public function isEmpty(): bool
    {
        return $this->entities->isEmpty();
    }

    public function getEntities(): ArrayCollection
    {
        return $this->entities;
    }

    public function getEntityClass(): ?string
    {
        return $this->entityClass;
    }

    public function setClassMetadataAdapter(ClassMetadataAdapter $classMetadataAdapter): void
    {
        $this->classMetadataAdapter = $classMetadataAdapter;
    }

    public function getClassMetadataAdapter(): ?ClassMetadataAdapter
    {
        return $this->classMetadataAdapter;
    }
}
