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

    /**
     * @param ArrayCollection $entities
     * @param null|string     $entityClass
     */
    public function __construct(ArrayCollection $entities, ?string $entityClass = null)
    {
        $this->entities = $entities;
        $this->entityClass = $entityClass;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->entities->isEmpty();
    }

    /**
     * @return ArrayCollection
     */
    public function getEntities(): ArrayCollection
    {
        return $this->entities;
    }

    /**
     * @return string|null
     */
    public function getEntityClass(): ?string
    {
        return $this->entityClass;
    }

    /**
     * @param ClassMetadataAdapter $classMetadataAdapter
     */
    public function setClassMetadataAdapter(ClassMetadataAdapter $classMetadataAdapter): void
    {
        $this->classMetadataAdapter = $classMetadataAdapter;
    }

    /**
     * @return ClassMetadataAdapter|null
     */
    public function getClassMetadataAdapter(): ?ClassMetadataAdapter
    {
        return $this->classMetadataAdapter;
    }
}
