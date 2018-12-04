<?php

namespace Malef\Associate\DoctrineOrm\Loader;

use Doctrine\ORM\Mapping\MappingException;
use Malef\Associate\AssociateException;
use Malef\Associate\DoctrineOrm\Association\AssociationTreeNode;
use Malef\Associate\DoctrineOrm\Loader\ArgumentConverter\AssociationsArgumentConverter;
use Malef\Associate\DoctrineOrm\Loader\ArgumentConverter\EntitiesArgumentConverter;
use Malef\Associate\DoctrineOrm\Association\AssociationTree;
use Malef\Associate\DoctrineOrm\Collector\AssociationCollector;
use Malef\Associate\DoctrineOrm\Metadata\MetadataAdapterProvider;
use Malef\Associate\DoctrineOrm\Source\EntitySource;

class EntityLoader
{
    /**
     * @var EntitiesArgumentConverter
     */
    protected $entitiesArgumentConverter;

    /**
     * @var AssociationsArgumentConverter
     */
    protected $associationsArgumentConverter;

    /**
     * @var MetadataAdapterProvider
     */
    protected $metadataAdapterProvider;

    /**
     * @var AssociationLoader
     */
    protected $associationLoader;

    /**
     * @var AssociationCollector
     */
    protected $associationCollector;

    /**
     * @var UninitializedProxiesLoader
     */
    protected $uninitializedProxiesLoader;

    /**
     * @param EntitiesArgumentConverter     $entitiesArgumentConverter
     * @param AssociationsArgumentConverter $associationsArgumentConverter
     * @param MetadataAdapterProvider       $metadataAdapterProvider
     * @param AssociationLoader             $associationLoader
     * @param AssociationCollector          $associationCollector
     * @param UninitializedProxiesLoader    $uninitializedProxiesLoader
     */
    public function __construct(
        EntitiesArgumentConverter $entitiesArgumentConverter,
        AssociationsArgumentConverter $associationsArgumentConverter,
        MetadataAdapterProvider $metadataAdapterProvider,
        AssociationLoader $associationLoader,
        AssociationCollector $associationCollector,
        UninitializedProxiesLoader $uninitializedProxiesLoader
    ) {
        $this->entitiesArgumentConverter = $entitiesArgumentConverter;
        $this->associationsArgumentConverter = $associationsArgumentConverter;
        $this->metadataAdapterProvider = $metadataAdapterProvider;
        $this->associationLoader = $associationLoader;
        $this->associationCollector = $associationCollector;
        $this->uninitializedProxiesLoader = $uninitializedProxiesLoader;
    }

    /**
     * @param iterable                        $entities
     * @param AssociationTree|string[]|string $associations
     * @param string|null                     $entityClass
     *
     * @throws \Exception
     */
    public function load(iterable $entities, $associations, ?string $entityClass): void
    {
        $rootEntities = $this->entitiesArgumentConverter->convertToEntitiesSource(
            $entities,
            $entityClass,
            $this->metadataAdapterProvider
        );

        if ($rootEntities->isEmpty()) {
            return;
        }

        $associationTree = $this->associationsArgumentConverter->convertToAssociationTree($associations);

        $this->uninitializedProxiesLoader->load(
            $rootEntities->getEntities(),
            $rootEntities->getClassMetadataAdapter()
        );

        /* @var AssociationTreeNode[] $nodes */
        $nodes = $associationTree->getPreOrderedNodes();

        $nodeToEntitiesMap = new \SplObjectStorage();

        foreach ($nodes as $node) {
            $this->loadEntitiesForAssociationTreeNode($node, $rootEntities, $nodeToEntitiesMap);
        }
    }

    /**
     * @param AssociationTreeNode $node
     * @param EntitySource        $rootEntities
     * @param \SplObjectStorage   $nodeToEntitiesMap
     *
     * @throws MappingException
     * @throws AssociateException
     */
    protected function loadEntitiesForAssociationTreeNode(
        AssociationTreeNode $node,
        EntitySource $rootEntities,
        \SplObjectStorage $nodeToEntitiesMap
    ): void {
        $parentNode = $node->getParent();

        /* var EntitySource $parentEntities */
        $parentEntities = ($parentNode instanceof AssociationTreeNode)
            ? $nodeToEntitiesMap->offsetGet($parentNode)
            : $rootEntities;

        $nodeAssociationMetadataAdapter = $this->metadataAdapterProvider
            ->getClassMetadataAdapterByClassName($parentEntities->getEntityClass())
            ->getAssociationMetadataAdapter($node->getAssociation()->getRelationshipName());

        $this->associationLoader->load(
            $parentEntities->getEntities(),
            $nodeAssociationMetadataAdapter
        );

        $nodeToEntitiesMap->offsetSet(
            $node,
            new EntitySource(
                $this->associationCollector->collect(
                    $parentEntities->getEntities(),
                    $nodeAssociationMetadataAdapter
                ),
                $nodeAssociationMetadataAdapter->getTargetClassName()
            )
        );
    }
}
