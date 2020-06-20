<?php

namespace Malef\Associate\DoctrineOrm\Association;

class Association
{
    /**
     * @var string
     */
    protected $relationshipName;

    /**
     * AssociationPath constructor.
     */
    public function __construct(string $relationshipName)
    {
        $this->relationshipName = $relationshipName;
    }

    public function getRelationshipName(): string
    {
        return $this->relationshipName;
    }
}
