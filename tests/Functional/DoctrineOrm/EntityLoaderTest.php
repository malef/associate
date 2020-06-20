<?php

namespace Malef\AssociateTests\Functional\DoctrineOrm;

use Doctrine\ORM\EntityManagerInterface;
use Malef\Associate\DoctrineOrm\Association\AssociationTree;
use Malef\Associate\DoctrineOrm\Facade;
use Malef\AssociateTests\Functional\DoctrineOrm\Mock\DoctrineOrmQueryLogger;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class EntityLoaderTest extends DatabaseTest
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

        $fixtureProvider = $this->getFixtureProvider();
        $this->entityManager = $fixtureProvider->getEntityManager();
        $this->queryLogger = $fixtureProvider->getQueryLogger();
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
        $facade
            ->createEntityLoader()
            ->load(
                $entities,
                $associationTree,
                $entityClass
            );

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

        $entities = $this->entityManager
            ->getRepository($repositoryEntityClass)
            ->findBy(['id' => $entityIds]);

        $entitiesIterable = $entitiesWrapCallback($entities);

        $this->queryLogger->clearLoggedQueries();
        $facade
            ->createEntityLoader()
            ->load($entitiesIterable, $associationTree, $loaderEntityClass);

        $this->assertCount($expectedQueriesCount, $this->queryLogger->getLoggedQueries());

        $this->queryLogger->clearLoggedQueries();
        foreach ($objectPaths as $objectPath) {
            $propertyNames = explode('.', $objectPath);
            $this->checkEntityProperties($entities, $propertyNames);
        }
        $this->assertCount(0, $this->queryLogger->getLoggedQueries());
    }
}
