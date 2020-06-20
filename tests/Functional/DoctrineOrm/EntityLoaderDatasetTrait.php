<?php

namespace Malef\AssociateTests\Functional\DoctrineOrm;

use Doctrine\Common\Collections\Collection;
use Malef\Associate\DoctrineOrm\Facade;
use Malef\AssociateTests\Functional\DoctrineOrm\Mock\BaseFixtureProvider;
use Malef\AssociateTests\Functional\DoctrineOrm\Mock\Entity\Bar;
use Malef\AssociateTests\Functional\DoctrineOrm\Mock\Entity\Baz;
use Malef\AssociateTests\Functional\DoctrineOrm\Mock\Entity\Foo;
use Malef\AssociateTests\Functional\DoctrineOrm\Mock\FixtureProvider;

trait EntityLoaderDatasetTrait
{
    protected function createDataProvider(): BaseFixtureProvider
    {
        return new FixtureProvider();
    }

    public function dataProviderLoad(): array
    {
        /* @var BaseFixtureProvider $genericDataProvider */
        $genericDataProvider = $this->getFixtureProvider();

        $genericDoctrineOrmFacade = new Facade(
            $genericDataProvider->getEntityManager()
        );

        return [
            [
                Foo::class,
                ['foo0'],
                $genericDoctrineOrmFacade
                    ->createAssociationTreeBuilder()
                    ->associate('bar')
                    ->create(),
                1,
                ['bar'],
            ],

            [
                Foo::class,
                ['foo0'],
                $genericDoctrineOrmFacade
                    ->createAssociationTreeBuilder()
                    ->associate('bar')
                    ->associate('foos')
                    ->create(),
                2,
                ['bar.foos'],
            ],

            [
                Foo::class,
                ['foo1'],
                $genericDoctrineOrmFacade
                    ->createAssociationTreeBuilder()
                    ->associate('bar')
                    ->create(),
                1,
                ['bar'],
            ],

            [
                Foo::class,
                ['foo1'],
                $genericDoctrineOrmFacade
                    ->createAssociationTreeBuilder()
                    ->associate('bar')
                    ->associate('foos')
                    ->create(),
                2,
                ['bar.foos'],
            ],

            [
                Foo::class,
                ['foo0'],
                $genericDoctrineOrmFacade
                    ->createAssociationTreeBuilder()
                    ->associate('bar')
                    ->associate('baz')
                    ->associate('foos')
                    ->create(),
                3 + 1, // One extra query is added when Doctrine hydrates one Baz instance and tries to load
                       // one-to-one relation bar for it.
                ['bar.baz.foos'],
            ],

            [
                Foo::class,
                ['foo0', 'foo1'],
                $genericDoctrineOrmFacade
                    ->createAssociationTreeBuilder()
                    ->associate('bar')
                    ->associate('baz')
                    ->associate('foos')
                    ->create(),
                3 + 2, // Two extra queries are added when Doctrine hydrates two Baz instances and tries to load
                       // one-to-one relationship bar for each of them.
                ['bar.baz.foos'],
            ],

            [
                Bar::class,
                ['bar0'],
                $genericDoctrineOrmFacade
                    ->createAssociationTreeBuilder()
                    ->associate('foos')
                    ->create(),
                1,
                ['foos'],
            ],

            [
                Foo::class,
                ['foo0', 'foo1', 'foo2'],
                $genericDoctrineOrmFacade
                    ->createAssociationTreeBuilder()
                    ->associate('bar')
                    ->associate('foos')
                    ->associate('bar')
                    ->create(),
                2,
                ['bar.foos.bar'],
            ],

            [
                Bar::class,
                ['bar0', 'bar1', 'bar2', 'bar3'],
                $genericDoctrineOrmFacade
                    ->createAssociationTreeBuilder()
                    ->associate('foos')
                    ->associate('bar')
                    ->create(),
                1,
                ['foos.bar'],
            ],

            [
                Foo::class,
                ['foo5'],
                $genericDoctrineOrmFacade
                    ->createAssociationTreeBuilder()
                    ->associate('bar')
                    ->associate('foos')
                    ->associate('bar')
                    ->associate('foos')
                    ->create(),
                2,
                ['bar.foos.bar.foos.bar'],
            ],

            [
                Bar::class,
                ['bar0', 'bar1', 'bar2', 'bar3'],
                $genericDoctrineOrmFacade
                    ->createAssociationTreeBuilder()
                    ->associate('foos')
                    ->associate('bar')
                    ->associate('foos')
                    ->associate('bar')
                    ->create(),
                1,
                ['foos.bar.foos.bar'],
            ],

            [
                Baz::class,
                ['baz0', 'baz1', 'baz2', 'baz3'],
                $genericDoctrineOrmFacade
                    ->createAssociationTreeBuilder()
                    ->associate('foos')
                    ->create(),
                1,
                ['foos'],
            ],

            [
                Baz::class,
                ['baz0', 'baz1', 'baz2', 'baz3'],
                $genericDoctrineOrmFacade
                    ->createAssociationTreeBuilder()
                    ->associate('foos')
                    ->associate('bar')
                    ->create(),
                2 - 1, // It is less by one as bars referenced via foos are the same as
                       // these having one-to-one relationship with starting bazs.
                ['foos.bar'],
            ],

            [
                Baz::class,
                ['baz0', 'baz4'],
                $genericDoctrineOrmFacade
                    ->createAssociationTreeBuilder()
                    ->associate('foos')
                    ->associate('bar')
                    ->create(),
                2,
                ['foos.bar'],
            ],
        ];
    }

    public function dataProviderVariousInputArgumentTypes(): array
    {
        /* @var BaseFixtureProvider $genericDataProvider */
        $genericDataProvider = $this->getFixtureProvider();

        $genericDoctrineOrmFacade = new Facade(
            $genericDataProvider->getEntityManager()
        );

        return [
            [
                Foo::class,
                Foo::class,
                ['foo0', 'foo1', 'foo2'],
                function (array $entities) {
                    return $entities;
                },
                $genericDoctrineOrmFacade
                    ->createAssociationTreeBuilder()
                    ->associate('bar')
                    ->create(),
                1,
                ['bar'],
            ],

            [
                Foo::class,
                null,
                ['foo0', 'foo1', 'foo2'],
                function (array $entities) {
                    return $entities;
                },
                $genericDoctrineOrmFacade
                    ->createAssociationTreeBuilder()
                    ->associate('bar')
                    ->create(),
                1,
                ['bar'],
            ],
        ];
    }

    /**
     * @param string[] $propertyNames
     *
     * @throws \UnexpectedValueException
     */
    protected function checkEntityProperties(array $entities, array $propertyNames): void
    {
        $propertyName = array_shift($propertyNames);
        foreach ($entities as $entity) {
            $childEntities = $this->getChildEntities($entity, $propertyName);
            foreach ($childEntities as $childEntity) {
                $childEntityAsString = (string) $childEntity;
            }
            if ($propertyNames) {
                $this->checkEntityProperties($childEntities, $propertyNames);
            }
        }
    }

    /**
     * @param mixed $entity
     *
     * @throws \UnexpectedValueException
     */
    protected function getChildEntities($entity, string $propertyName): array
    {
        $propertyValue = $this->propertyAccessor->getValue($entity, $propertyName);
        if (is_null($propertyValue)) {
            return [];
        }

        if (!$propertyValue instanceof Collection) {
            return [$propertyValue];
        }

        if ($propertyValue instanceof Collection) {
            return $propertyValue->getValues();
        }

        // This code should not be reached.
        // @phpstan-ignore-next-line
        throw new \UnexpectedValueException();
    }
}
