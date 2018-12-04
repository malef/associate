<?php

namespace Malef\Associate\DoctrineOrm\Association;

use Tree\Builder\NodeBuilder;
use Tree\Node\Node;
use Tree\Node\NodeInterface;

class AssociationTreeBuilder
{
    /**
     * @var NodeBuilder
     */
    protected $nodeBuilder;

    /**
     * @var NodeInterface
     */
    protected $rootNode;

    /**
     * @var bool
     */
    protected $isAllowedToAlterAssociation;

    /**
     * @var NodeInterface[]
     */
    protected $divergeNodes = [];

    public function __construct()
    {
        $this->rootNode = new Node();
        $this->isAllowedToAlterAssociation = false;
        $this->nodeBuilder = new NodeBuilder($this->rootNode);
    }

    /**
     * @param string $relationshipName
     *
     * @return self
     *
     * @throws \Exception
     */
    public function associate(string $relationshipName): self
    {
        $this->nodeBuilder->tree(new Association($relationshipName));
        $this->isAllowedToAlterAssociation = true;

        return $this;
    }

    /**
     * @return self
     */
    public function diverge(): self
    {
        $this->divergeNodes[] = $this->nodeBuilder->getNode();
        $this->isAllowedToAlterAssociation = false;

        return $this;
    }

    /**
     * @return self
     *
     * @throws \Exception
     */
    public function endDiverge(): self
    {
        if (!$this->divergeNodes) {
            throw new \Exception();
        }

        $lastDivergeNode = array_pop($this->divergeNodes);
        while ($this->nodeBuilder->getNode() !== $lastDivergeNode) {
            $this->nodeBuilder->end();
        }
        $this->isAllowedToAlterAssociation = false;

        return $this;
    }

    /**
     * @return AssociationTree
     */
    public function create(): AssociationTree
    {
        return new AssociationTree($this->rootNode);
    }
}
