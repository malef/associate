<?php

namespace Malef\AssociateTests\Functional\DoctrineOrm\Mock;

use Malef\AssociateTests\Functional\DoctrineOrm\Mock\Entity\Bar;
use Malef\AssociateTests\Functional\DoctrineOrm\Mock\Entity\Baz;
use Malef\AssociateTests\Functional\DoctrineOrm\Mock\Entity\Foo;
use Malef\AssociateTests\Functional\DoctrineOrm\Mock\Entity\Qux;

class FixtureProvider extends BaseFixtureProvider
{
    /**
     * {@inheritdoc}
     */
    protected function getDatasetProvider(): AliceDatasetProviderInterface
    {
        return new AliceDatasetProvider();
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntityPaths(): array
    {
        return [
            __DIR__ . '/Entity',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntityClassNames(): array
    {
        return [
            Foo::class,
            Bar::class,
            Baz::class,
            Qux::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getTableNames(): array
    {
        return [
            'foo',
            'bar',
            'baz',
            'qux',
            'foo_baz',
            'baz_qux',
            'foo_qux',
        ];
    }
}
