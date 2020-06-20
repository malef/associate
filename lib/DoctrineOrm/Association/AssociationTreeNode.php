<?php

namespace Malef\Associate\DoctrineOrm\Association;

class AssociationTreeNode
{
    /**
     * @var Association
     */
    protected $association;

    /**
     * @var self|null
     */
    protected $parent;

    public function __construct(Association $association, ?self $parent)
    {
        $this->association = $association;
        $this->parent = $parent;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function getAssociation(): Association
    {
        return $this->association;
    }
}
