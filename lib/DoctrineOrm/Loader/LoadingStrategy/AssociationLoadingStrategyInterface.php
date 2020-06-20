<?php

namespace Malef\Associate\DoctrineOrm\Loader\LoadingStrategy;

use Doctrine\Common\Collections\ArrayCollection;
use Malef\Associate\DoctrineOrm\Metadata\AssociationMetadataAdapter;

interface AssociationLoadingStrategyInterface
{
    public function supports(AssociationMetadataAdapter $associationMetadataAdapter): bool;

    public function load(ArrayCollection $sourceEntities, AssociationMetadataAdapter $associationMetadataAdapter): void;
}
