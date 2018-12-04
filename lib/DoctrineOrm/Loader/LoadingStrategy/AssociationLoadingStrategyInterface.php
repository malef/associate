<?php

namespace Malef\Associate\DoctrineOrm\Loader\LoadingStrategy;

use Malef\Associate\DoctrineOrm\Metadata\AssociationMetadataAdapter;
use Doctrine\Common\Collections\ArrayCollection;

interface AssociationLoadingStrategyInterface
{
    public function supports(AssociationMetadataAdapter $associationMetadataAdapter): bool;

    public function load(ArrayCollection $sourceEntities, AssociationMetadataAdapter $associationMetadataAdapter): void;
}
