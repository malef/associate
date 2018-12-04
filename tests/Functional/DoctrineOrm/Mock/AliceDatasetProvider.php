<?php

namespace Malef\AssociateTests\Functional\DoctrineOrm\Mock;

use Malef\AssociateTests\Functional\DoctrineOrm\Mock\Entity\Bar;
use Malef\AssociateTests\Functional\DoctrineOrm\Mock\Entity\Baz;
use Malef\AssociateTests\Functional\DoctrineOrm\Mock\Entity\Foo;
use Malef\AssociateTests\Functional\DoctrineOrm\Mock\Entity\Qux;

class AliceDatasetProvider implements AliceDatasetProviderInterface
{
    public function provide(): array
    {
        $dataset = [
            Foo::class => [
                'foo0' => [
                    'id' => 'foo0',
                    'payload' => '<uuid()>',
                    'bar' => '@bar0',
                    'bazs' => ['@baz0', '@baz1'],
                    'quxs' => [],
                ],
                'foo1' => [
                    'id' => 'foo1',
                    'payload' => '<uuid()>',
                    'bar' => '@bar1',
                    'bazs' => ['@baz2'],
                    'quxs' => [],
                ],
                'foo2' => [
                    'id' => 'foo2',
                    'payload' => '<uuid()>',
                    'bar' => '@bar1',
                    'bazs' => ['@baz2'],
                    'quxs' => [],
                ],
                'foo3' => [
                    'id' => 'foo3',
                    'payload' => '<uuid()>',
                    'bar' => '@bar1',
                    'bazs' => ['@baz2'],
                    'quxs' => [],
                ],
                'foo4' => [
                    'id' => 'foo4',
                    'payload' => '<uuid()>',
                    'bar' => '@bar1',
                    'bazs' => ['@baz3'],
                    'quxs' => [],
                ],
                'foo5' => [
                    'id' => 'foo5',
                    'payload' => '<uuid()>',
                    'bar' => '@bar2',
                    'bazs' => ['@baz4'],
                    'quxs' => [],
                ],
                'foo6' => [
                    'id' => 'foo6',
                    'payload' => '<uuid()>',
                    'bar' => '@bar3',
                    'bazs' => [],
                    'quxs' => [],
                ],
                'foo7' => [
                    'id' => 'foo7',
                    'payload' => '<uuid()>',
                    'bar' => '@bar4',
                    'bazs' => ['@baz4'],
                    'quxs' => [],
                ],
                'foo8' => [
                    'id' => 'foo8',
                    'payload' => '<uuid()>',
                    'bar' => null,
                    'bazs' => [],
                    'quxs' => [],
                ],
                'foo9' => [
                    'id' => 'foo9',
                    'payload' => '<uuid()>',
                    'bar' => null,
                    'bazs' => [],
                    'quxs' => [],
                ],
            ],

            Bar::class => [
                'bar0' => [
                    'id' => 'bar0',
                    'payload' => '<uuid()>',
                    'baz' => '@baz0',
                ],
                'bar1' => [
                    'id' => 'bar1',
                    'payload' => '<uuid()>',
                    'baz' => '@baz1',
                ],
                'bar2' => [
                    'id' => 'bar2',
                    'payload' => '<uuid()>',
                    'baz' => '@baz2',
                ],
                'bar3' => [
                    'id' => 'bar3',
                    'payload' => '<uuid()>',
                    'baz' => '@baz3',
                ],
                'bar4' => [
                    'id' => 'bar4',
                    'payload' => '<uuid()>',
                    'baz' => null,
                ],
            ],

            Baz::class => [
                'baz0' => [
                    'id' => 'baz0',
                    'payload' => '<uuid()>',
                    'quxs' => ['@qux0'],
                ],
                'baz1' => [
                    'id' => 'baz1',
                    'payload' => '<uuid()>',
                ],
                'baz2' => [
                    'id' => 'baz2',
                    'payload' => '<uuid()>',
                ],
                'baz3' => [
                    'id' => 'baz3',
                    'payload' => '<uuid()>',
                ],
                'baz4' => [
                    'id' => 'baz4',
                    'payload' => '<uuid()>',
                ],
                'baz5' => [
                    'id' => 'baz5',
                    'payload' => '<uuid()>',
                ],
                'baz6' => [
                    'id' => 'baz6',
                    'payload' => '<uuid()>',
                ],
                'baz7' => [
                    'id' => 'baz7',
                    'payload' => '<uuid()>',
                ],
                'baz8' => [
                    'id' => 'baz8',
                    'payload' => '<uuid()>',
                ],
            ],

            Qux::class => [
                'qux0' => [
                    'id' => 'qux0',
                    'payload' => '<uuid()>',
                ],
            ],
        ];

        return $dataset;
    }
}
