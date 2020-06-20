<?php

namespace Malef\Associate\DoctrineOrm\Loader\ArgumentConverter;

use Malef\Associate\DoctrineOrm\Association\AssociationTree;
use Malef\Associate\DoctrineOrm\Association\AssociationTreeBuilder;

class AssociationsArgumentConverter
{
    /**
     * @param AssociationTree|string|string[] $associations
     *
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function convertToAssociationTree($associations): AssociationTree
    {
        if ($associations instanceof AssociationTree) {
            return $associations;
        }

        $associationsTreeBuilder = new AssociationTreeBuilder();

        if (is_string($associations)) {
            $associations = explode('.', $associations);
        }

        if (is_array($associations)) {
            foreach ($associations as $association) {
                $associationsTreeBuilder->associate($association);
            }

            return $associationsTreeBuilder->create();
        }

        // This code should not be reached.
        // @phpstan-ignore-next-line
        throw new \InvalidArgumentException();
    }
}
