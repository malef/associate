<?php

namespace Malef\Associate\DoctrineOrm\Metadata;

use Doctrine\ORM\Mapping\ClassMetadataInfo;

class AssociationMetadataAdapter
{
    /**
     * @var MetadataAdapterProvider
     */
    protected $metadataAdapterProvider;

    /**
     * @var array
     */
    protected $associationMapping;

    /**
     * @param MetadataAdapterProvider $metadataAdapterProvider
     * @param array                   $associationMapping
     */
    public function __construct(
        MetadataAdapterProvider $metadataAdapterProvider,
        array $associationMapping
    ) {
        $this->metadataAdapterProvider = $metadataAdapterProvider;
        $this->associationMapping = $associationMapping;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->associationMapping['fieldName'];
    }

    /**
     * @return bool
     */
    public function isOwningSide(): bool
    {
        return $this->associationMapping['isOwningSide'];
    }

    /**
     * @return bool
     */
    public function isInverseSide(): bool
    {
        return !$this->isOwningSide();
    }

    /**
     * @return bool
     */
    public function isOneToOne(): bool
    {
        return ClassMetadataInfo::ONE_TO_ONE === $this->associationMapping['type'];
    }

    /**
     * @return bool
     */
    public function isOneToMany(): bool
    {
        return ClassMetadataInfo::ONE_TO_MANY === $this->associationMapping['type'];
    }

    /**
     * @return bool
     */
    public function isManyToOne(): bool
    {
        return ClassMetadataInfo::MANY_TO_ONE === $this->associationMapping['type'];
    }

    /**
     * @return bool
     */
    public function isManyToMany(): bool
    {
        return ClassMetadataInfo::MANY_TO_MANY === $this->associationMapping['type'];
    }

    /**
     * @return bool
     */
    public function isToMany(): bool
    {
        return $this->isManyToMany() || $this->isOneToMany();
    }

    /**
     * @return string
     */
    public function getSourceClassName(): string
    {
        return $this->associationMapping['sourceEntity'];
    }

    /**
     * @return ClassMetadataAdapter
     *
     * @throws \Exception
     */
    public function getSourceClassMetadataAdapter(): ClassMetadataAdapter
    {
        $sourceClassName = $this->metadataAdapterProvider->getClassMetadataAdapterByClassName($this->getSourceClassName());
        if (!$sourceClassName instanceof ClassMetadataAdapter) {
            throw new \Exception('Source class name not determined.');
        }

        return $sourceClassName;
    }

    /**
     * @return string
     */
    public function getTargetClassName(): string
    {
        return $this->associationMapping['targetEntity'];
    }

    /**
     * @return ClassMetadataAdapter
     *
     * @throws \Exception
     */
    public function getTargetClassMetadataAdapter(): ClassMetadataAdapter
    {
        $targetClassName = $this->metadataAdapterProvider->getClassMetadataAdapterByClassName($this->getTargetClassName());
        if (!$targetClassName instanceof ClassMetadataAdapter) {
            throw new \Exception('Target class name not determined.');
        }

        return $targetClassName;
    }
}
