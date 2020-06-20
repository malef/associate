<?php

namespace Malef\Associate\DoctrineOrm\Metadata;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Malef\Associate\AssociateException;

class MetadataAdapterProvider
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var (ClassMetadataAdapter|null)[]
     */
    protected $classMetadataAdapters = [];

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @throws AssociateException
     */
    public function getClassNameForEntities(ArrayCollection $objects): string
    {
        if ($objects->isEmpty()) {
            throw new AssociateException();
        }

        $firstObject = $objects->first();
        $classMetadataAdapter = $this->getClassMetadataAdapterByClassName(get_class($firstObject));
        if (!$classMetadataAdapter instanceof ClassMetadataAdapter) {
            throw new AssociateException();
        }
        $commonClassName = $classMetadataAdapter->getClassName();

        foreach ($objects->slice(1) as $object) {
            if (is_a($object, $commonClassName, true)) {
                continue;
            }
            $commonClassName = $classMetadataAdapter->getRootClassName();
            if (!is_a($object, $commonClassName, true)) {
                throw new AssociateException();
            }
        }

        return $commonClassName;
    }

    /**
     * @throws \Exception
     */
    public function getClassMetadataAdapterByClassName(string $className): ?ClassMetadataAdapter
    {
        if (!array_key_exists($className, $this->classMetadataAdapters)) {
            $this->initializeClassMetadataAdapterByClassName($className);
        }

        return $this->classMetadataAdapters[$className];
    }

    /**
     * @throws AssociateException
     */
    protected function initializeClassMetadataAdapterByClassName(string $className): void
    {
        $classMetadata = $this->entityManager->getClassMetadata($className);
        if (!$classMetadata instanceof ClassMetadata) {
            $this->classMetadataAdapters[$className] = null;

            return;
        }

        $entityRepository = $this->entityManager->getRepository($className);
        if (!$entityRepository instanceof EntityRepository) {
            throw new AssociateException();
        }
        $this->classMetadataAdapters[$className] = new ClassMetadataAdapter($this, $entityRepository, $classMetadata);
    }
}
