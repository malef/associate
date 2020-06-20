<?php

namespace Malef\AssociateTests\Functional\DoctrineOrm;

use Doctrine\ORM\EntityManagerInterface;
use GraphQL\Deferred;
use Malef\Associate\DoctrineOrm\Association\AssociationTree;
use Malef\Associate\DoctrineOrm\Facade;
use Malef\AssociateTests\Functional\DoctrineOrm\Mock\DoctrineOrmQueryLogger;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class DeferredEntityLoaderTest extends DatabaseTest
{
    use EntityLoaderDatasetTrait;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var DoctrineOrmQueryLogger
     */
    protected $queryLogger;

    /**
     * @var PropertyAccessor
     */
    protected $propertyAccessor;

    protected function setUp(): void
    {
        parent::setUp();

        $dataProvider = $this->getFixtureProvider();
        $this->entityManager = $dataProvider->getEntityManager();
        $this->queryLogger = $dataProvider->getQueryLogger();
        $this->entityManager->clear();

        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @param string[] $entityIds
     * @param string[] $objectPaths
     *
     * @dataProvider dataProviderLoad
     */
    public function testLoad(
        string $entityClass,
        array $entityIds,
        AssociationTree $associationTree,
        int $expectedQueriesCount,
        array $objectPaths
    ) {
        $facade = new Facade($this->entityManager);

        $entities = $this->entityManager->getRepository($entityClass)->findBy(['id' => $entityIds]);
        $this->queryLogger->clearLoggedQueries();

        $deferredEntityLoaderFactory = $facade->createDeferredEntityLoaderFactory();

        $deferredEntityLoader = $deferredEntityLoaderFactory->create(
            $associationTree,
            $entityClass
        );

        foreach ($entities as $entity) {
            $deferredEntityLoader->createDeferred(
                [$entity],
                function () {}
            );
        }

        Deferred::runQueue();

        $this->assertCount($expectedQueriesCount, $this->queryLogger->getLoggedQueries());

        $this->queryLogger->clearLoggedQueries();
        foreach ($objectPaths as $objectPath) {
            $propertyNames = explode('.', $objectPath);
            $this->checkEntityProperties($entities, $propertyNames);
        }
        $this->assertCount(0, $this->queryLogger->getLoggedQueries());
    }

    /**
     * @param string                       $repositoryEntityClass,
     * @param ?string                      $loaderEntityClass,
     * @param string[]                     $entityIds
     * @param AssociationTree|string|array $associationTree
     * @param string[]                     $objectPaths
     *
     * @dataProvider dataProviderVariousInputArgumentTypes
     */
    public function testVariousInputArgumentTypes(
        string $repositoryEntityClass,
        ?string $loaderEntityClass,
        array $entityIds,
        \Closure $entitiesWrapCallback,
        $associationTree,
        int $expectedQueriesCount,
        array $objectPaths
    ) {
        $facade = new Facade($this->entityManager);

        $entities = $this->entityManager->getRepository($repositoryEntityClass)->findBy(['id' => $entityIds]);

        $this->queryLogger->clearLoggedQueries();

        $deferredEntityLoaderFactory = $facade->createDeferredEntityLoaderFactory();

        $deferredEntityLoader = $deferredEntityLoaderFactory->create(
            $associationTree,
            $loaderEntityClass
        );

        foreach ($entities as $entity) {
            $deferredEntityLoader->createDeferred(
                $entitiesWrapCallback([$entity]),
                function () {}
            );
        }

        Deferred::runQueue();

        $this->assertCount($expectedQueriesCount, $this->queryLogger->getLoggedQueries());

        $this->queryLogger->clearLoggedQueries();
        foreach ($objectPaths as $objectPath) {
            $propertyNames = explode('.', $objectPath);
            $this->checkEntityProperties($entities, $propertyNames);
        }
        $this->assertCount(0, $this->queryLogger->getLoggedQueries());
    }
}
