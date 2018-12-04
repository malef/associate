<?php

namespace Malef\Associate\DoctrineOrm\Metadata;

use Malef\Associate\AssociateException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;

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

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param ArrayCollection $objects
     *
     * @return string
     *
     * @throws AssociateException
     */
    public function getClassNameForEntities(ArrayCollection $objects): string
    {
        if (!$objects) {
            throw new AssociateException();
        }

        $firstObject = $objects->first();
        $classMetadataAdapter = $this->getClassMetadataAdapterByClassName(get_class($firstObject));
        if (!$classMetadataAdapter instanceof ClassMetadataAdapter) {
            throw new AssociateException();
        }
        $commonClassName = $classMetadataAdapter->getClassName();
        $rootClassNameUsed = false;

        foreach ($objects->slice(1) as $object) {
            if (is_a($object, $commonClassName, true)) {
                continue;
            }
            if ($rootClassNameUsed) {
                throw new AssociateException();
            }
            $commonClassName = $classMetadataAdapter->getRootClassName();
            if (!is_a($object, $commonClassName, true)) {
                throw new AssociateException();
            }
        }

        return $commonClassName;
    }

    /**
     * @param string $className
     *
     * @return ClassMetadataAdapter|null
     *
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
     * @param string $className
     *
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
