<?php

namespace Malef\AssociateTests\Unit\DoctrineOrm\Association;

use Malef\Associate\DoctrineOrm\Association\AssociationTree;
use Malef\Associate\DoctrineOrm\Association\AssociationTreeBuilder;
use PHPUnit\Framework\TestCase;

class AssociationTreeBuilderSmokeTest extends TestCase
{
    public function testSmoke1()
    {
        $associationTreeBuilder = new AssociationTreeBuilder();

        $associationTree = $associationTreeBuilder
            ->associate('bar')
            ->associate('baz')
            ->associate('qux')
            ->create();

        $this->assertInstanceOf(AssociationTree::class, $associationTree);
        $this->assertSame(
            [
                '.bar.baz.qux',
            ],
            $this->convertAssociationTreeToStrings($associationTree)
        );
    }

    public function testSmoke2()
    {
        $associationTreeBuilder = new AssociationTreeBuilder();

        $associationTree = $associationTreeBuilder
            ->associate('bar')
            ->associate('baz')
            ->associate('qux')
            ->create();

        $this->assertInstanceOf(AssociationTree::class, $associationTree);
        $this->assertSame(
            [
                '.bar.baz.qux',
            ],
            $this->convertAssociationTreeToStrings($associationTree)
        );
    }

    public function testSmoke3()
    {
        $associationTreeBuilder = new \Malef\Associate\DoctrineOrm\Association\AssociationTreeBuilder();

        $associationTree = $associationTreeBuilder
            ->diverge()
                ->associate('foo')
                ->diverge()
                    ->associate('bar')
                ->endDiverge()
                ->diverge()
                    ->associate('baz')
                    ->associate('qux')
                ->endDiverge()
            ->endDiverge()
            ->diverge()
                ->associate('qux')
            ->endDiverge()
            ->create();

        $this->assertInstanceOf(AssociationTree::class, $associationTree);
        $this->assertSame(
            [
                '.foo.bar',
                '    .baz.qux',
                '.qux',
            ],
            $this->convertAssociationTreeToStrings($associationTree)
        );
    }

    public function testSmoke4()
    {
        $associationTreeBuilder = new AssociationTreeBuilder();

        $associationTree = $associationTreeBuilder
            ->associate('foo')
            ->associate('bar')
            ->diverge()
                ->associate('baz')
                ->associate('qux')
            ->endDiverge()
            ->diverge()
                ->associate('qux')
            ->endDiverge()
            ->create();

        $this->assertInstanceOf(AssociationTree::class, $associationTree);
        $this->assertSame(
            [
                '.foo.bar.baz.qux',
                '        .qux',
            ],
            $this->convertAssociationTreeToStrings($associationTree)
        );
    }

    public function testSmoke5()
    {
        $associationTreeBuilder = new \Malef\Associate\DoctrineOrm\Association\AssociationTreeBuilder();

        $associationTree = $associationTreeBuilder
            ->diverge()
                ->associate('foo')
            ->endDiverge()
            ->diverge()
                ->associate('bar')
            ->endDiverge()
            ->diverge()
                ->associate('baz')
                ->associate('qux')
            ->endDiverge()
            ->diverge()
                ->associate('qux')
            ->endDiverge()
            ->create();

        $this->assertInstanceOf(AssociationTree::class, $associationTree);
        $this->assertSame(
            [
                '.foo',
                '.bar',
                '.baz.qux',
                '.qux',
            ],
            $this->convertAssociationTreeToStrings($associationTree)
        );
    }

    public function testSmoke6()
    {
        $associationTreeBuilder = new AssociationTreeBuilder();

        $associationTree = $associationTreeBuilder
            ->diverge()
                ->associate('foo')
                ->diverge()
                    ->associate('bar')
                    ->diverge()
                        ->associate('baz')
                        ->associate('qux')
                        ->diverge()
                            ->associate('qux')
            ->create();

        $this->assertInstanceOf(AssociationTree::class, $associationTree);
        $this->assertSame(
            [
                '.foo.bar.baz.qux.qux',
            ],
            $this->convertAssociationTreeToStrings($associationTree)
        );
    }

    /**
     * @return string[]
     */
    protected function convertAssociationTreeToStrings(AssociationTree $associationTree): array
    {
        $dumpLines = [];
        foreach ($associationTree->getAssociationPaths() as $associationPath) {
            $dumpLine = '';
            foreach ($associationPath->getAssociations() as $association) {
                $dumpLine .= sprintf('.%s', $association->getRelationshipName());
            }
            $nestedTree = $associationPath->getNestedAssociationTree();
            if ($nestedTree instanceof AssociationTree) {
                $nestedTreeDumpLines = $this->convertAssociationTreeToStrings($associationPath->getNestedAssociationTree());
                foreach ($nestedTreeDumpLines as $index => $nestedTreeDumpLine) {
                    $dumpLinePrefix = 0 === $index
                        ? $dumpLine
                        : str_repeat(' ', strlen($dumpLine));
                    $dumpLines[] = $dumpLinePrefix . $nestedTreeDumpLine;
                }
            } else {
                $dumpLines[] = $dumpLine;
            }
        }

        return $dumpLines;
    }
}
