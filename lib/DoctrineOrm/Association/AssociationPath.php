<?php

namespace Malef\Associate\DoctrineOrm\Association;

class AssociationPath
{
    /**
     * @var Association[]
     */
    protected $associations = [];

    /**
     * @var AssociationTree|null
     */
    protected $nestedAssociationTree;

    public function __construct(array $associations, ?AssociationTree $nestedAssociationTree = null)
    {
        $this->associations = $associations;
        $this->nestedAssociationTree = $nestedAssociationTree;
    }

    /**
     * @return Association[]
     */
    public function getAssociations(): array
    {
        return $this->associations;
    }

    public function getNestedAssociationTree(): ?AssociationTree
    {
        return $this->nestedAssociationTree;
    }
}
