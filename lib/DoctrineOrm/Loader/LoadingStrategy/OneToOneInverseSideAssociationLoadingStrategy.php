<?php

namespace Malef\Associate\DoctrineOrm\Loader\LoadingStrategy;

use Malef\Associate\DoctrineOrm\Metadata\AssociationMetadataAdapter;
use Doctrine\Common\Collections\ArrayCollection;

class OneToOneInverseSideAssociationLoadingStrategy implements AssociationLoadingStrategyInterface
{
    public function supports(AssociationMetadataAdapter $associationMetadataAdapter): bool
    {
        return $associationMetadataAdapter->isOneToOne() && $associationMetadataAdapter->isInverseSide();
    }

    public function load(ArrayCollection $sourceEntities, AssociationMetadataAdapter $associationMetadataAdapter): void
    {
        // Nothing to do here.
    }
}
