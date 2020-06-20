<?php

namespace Malef\AssociateTests\Functional\DoctrineOrm\Mock;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use Nelmio\Alice\Loader\NativeLoader;

abstract class BaseFixtureProvider
{
    /** @var EntityManagerInterface */
    protected static $entityManager;

    /** @var DoctrineOrmQueryLogger */
    protected static $queryLogger;

    public function getEntityManager(): EntityManagerInterface
    {
        if (!self::$entityManager instanceof EntityManagerInterface) {
            $this->setupEntityManager();
        }

        return self::$entityManager;
    }

    public function getQueryLogger(): DoctrineOrmQueryLogger
    {
        return self::$queryLogger;
    }

    /**
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public function createSchema(): void
    {
        $this->getEntityManager();

        $entityClassMetadatas = [];
        foreach ($this->getEntityClassNames() as $entityClassName) {
            $entityClassMetadatas[] = self::$entityManager->getClassMetadata($entityClassName);
        }

        $tool = new SchemaTool(self::$entityManager);
        $tool->createSchema($entityClassMetadatas);
    }

    /**
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public function dropSchema(): void
    {
        $this->getEntityManager();

        $entityClassMetadatas = [];
        foreach ($this->getEntityClassNames() as $entityClassName) {
            $entityClassMetadatas[] = self::$entityManager->getClassMetadata($entityClassName);
        }

        $tool = new SchemaTool(self::$entityManager);
        $tool->dropSchema($entityClassMetadatas);
    }

    public function loadData(): void
    {
        $this->getEntityManager();

        $datasetProvider = $this->getDatasetProvider();

        $loader = new NativeLoader();
        $objectSet = $loader->loadData($datasetProvider->provide());

        foreach ($objectSet->getObjects() as $entity) {
            self::$entityManager->persist($entity);
        }
        self::$entityManager->flush();
    }

    public function getArrayDataSetRawData(): array
    {
        $this->getEntityManager();

        $datasetProvider = $this->getDatasetProvider();

        $loader = new NativeLoader();
        $objectSet = $loader->loadData($datasetProvider->provide());

        $tableNames = $this->getTableNames();

        $tableNames = array_unique(array_values($tableNames));
        $arrayDataSetRawData = array_combine(
            $tableNames,
            array_fill(0, count($tableNames), [])
        );

        foreach ($objectSet->getObjects() as $object) {
            foreach ($object->toArrayDataSetItemsMap() as $tableName => $rows) {
                $arrayDataSetRawData[$tableName] = array_merge(
                    $arrayDataSetRawData[$tableName],
                    $rows
                );
            }
        }

        return $arrayDataSetRawData;
    }

    protected function setupEntityManager(): void
    {
        $entityPaths = $this->getEntityPaths();
        $isDevMode = true;
        $databaseOptions = [
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ];

        $metadataConfiguration = Setup::createAnnotationMetadataConfiguration($entityPaths, $isDevMode);
        self::$entityManager = EntityManager::create($databaseOptions, $metadataConfiguration);

        self::$queryLogger = new DoctrineOrmQueryLogger();
        self::$entityManager->getConnection()->getConfiguration()->setSQLLogger(self::$queryLogger);
    }

    abstract protected function getDatasetProvider(): AliceDatasetProviderInterface;

    /**
     * @return string[]
     */
    abstract protected function getEntityPaths(): array;

    /**
     * @return string[]
     */
    abstract protected function getEntityClassNames(): array;

    /**
     * @return string[]
     */
    abstract protected function getTableNames(): array;
}
