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
     *
     * @param string $relationshipName
     */
    public function __construct(string $relationshipName)
    {
        $this->relationshipName = $relationshipName;
    }

    /**
     * @return string
     */
    public function getRelationshipName(): string
    {
        return $this->relationshipName;
    }
}
