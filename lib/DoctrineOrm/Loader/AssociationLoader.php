<?php

namespace Malef\Associate\DoctrineOrm\Loader;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Malef\Associate\AssociateException;
use Malef\Associate\DoctrineOrm\Loader\LoadingStrategy\AssociationLoadingStrategyInterface;
use Malef\Associate\DoctrineOrm\Loader\LoadingStrategy\OneToOneInverseSideAssociationLoadingStrategy;
use Malef\Associate\DoctrineOrm\Loader\LoadingStrategy\ToManyAssociationLoadingStrategy;
use Malef\Associate\DoctrineOrm\Loader\LoadingStrategy\ToOneAssociationLoadingStrategy;
use Malef\Associate\DoctrineOrm\Metadata\AssociationMetadataAdapter;

class AssociationLoader
{
    /**
     * @var Collection|AssociationLoadingStrategyInterface[]
     */
    protected $associationLoadingStrategies;

    public function __construct(
        OneToOneInverseSideAssociationLoadingStrategy $oneToOneInverseSideAssociationLoadingStrategy,
        ToOneAssociationLoadingStrategy $toOneAssociationLoadingStrategy,
        ToManyAssociationLoadingStrategy $toManyAssociationLoadingStrategy
    ) {
        $this->associationLoadingStrategies = new ArrayCollection([
            $oneToOneInverseSideAssociationLoadingStrategy,
            $toOneAssociationLoadingStrategy,
            $toManyAssociationLoadingStrategy,
        ]);
    }

    /**
     * @throws AssociateException if no association loading strategy
     *                            or multiple association loading strategies are found
     */
    public function load(
        ArrayCollection $sourceEntities,
        AssociationMetadataAdapter $associationMetadataAdapter
    ): void {
        $this
            ->getSupportingAssociationLoadingStrategy($associationMetadataAdapter)
            ->load($sourceEntities, $associationMetadataAdapter);
    }

    protected function getSupportingAssociationLoadingStrategy(
        AssociationMetadataAdapter $associationMetadataAdapter
    ): AssociationLoadingStrategyInterface {
        $supportingAssociationLoadingStrategies = $this->associationLoadingStrategies->filter(
            function (AssociationLoadingStrategyInterface $associationLoadingStrategy) use ($associationMetadataAdapter) {
                return $associationLoadingStrategy->supports($associationMetadataAdapter);
            }
        );

        if ($supportingAssociationLoadingStrategies->isEmpty()) {
            throw new AssociateException('No association loading strategy found.');
        }

        if (1 < $supportingAssociationLoadingStrategies->count()) {
            throw new AssociateException('Multiple association loading strategies found.');
        }

        return $supportingAssociationLoadingStrategies->first();
    }
}
