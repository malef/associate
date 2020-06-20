<?php

namespace Malef\Associate\DoctrineOrm\Association;

use Tree\Node\NodeInterface;
use Tree\Visitor\PreOrderVisitor;

class AssociationTree
{
    /**
     * @var NodeInterface
     */
    protected $rootNode;

    public function __construct(NodeInterface $rootNode)
    {
        $this->rootNode = $rootNode;
    }

    /**
     * @return AssociationPath[]
     */
    public function getAssociationPaths(): array
    {
        $associationPaths = [];
        foreach ($this->rootNode->getChildren() as $childNode) {
            $associationPaths[] = $this->getAssociationPath($childNode);
        }

        return $associationPaths;
    }

    /**
     * @return AssociationTreeNode[]
     */
    public function getPreOrderedNodes(): array
    {
        /* @var NodeInterface[] $nodes */
        // External library uses invalid typehint for return value.
        // @phpstan-ignore-next-line
        $nodes = $this->rootNode->accept(new PreOrderVisitor());
        $rootNode = array_shift($nodes);
        $associationTreeNodes = [];
        $nodeToAssociationTreeNodeMap = new \SplObjectStorage();
        // External library uses invalid typehint for return value.
        // @phpstan-ignore-next-line
        foreach ($nodes as $node) {
            $parentNode = $node->getParent();
            $associationTreeNode = new AssociationTreeNode(
                $node->getValue(),
                ($parentNode instanceof NodeInterface && $parentNode !== $rootNode)
                    ? $nodeToAssociationTreeNodeMap->offsetGet($parentNode)
                    : null
            );
            $nodeToAssociationTreeNodeMap->attach($node, $associationTreeNode);
            $associationTreeNodes[] = $associationTreeNode;
        }

        return $associationTreeNodes;
    }

    protected function getAssociationPath(NodeInterface $node): AssociationPath
    {
        $associations = [];
        do {
            $associations[] = $node->getValue();
            $childNodes = $node->getChildren();
            if ($node->isLeaf()) {
                return new AssociationPath($associations);
            }
            if (count($childNodes) > 1) {
                return new AssociationPath($associations, new self($node));
            }
            $node = reset($childNodes);
        } while (true);
    }
}
