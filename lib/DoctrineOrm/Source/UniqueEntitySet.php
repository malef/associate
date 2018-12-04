<?php

namespace Malef\Associate\DoctrineOrm\Source;

use Doctrine\Common\Collections\ArrayCollection;

class UniqueEntitySet
{
    /**
     * @var \SplObjectStorage
     */
    protected $entities;

    public function __construct()
    {
        $this->entities = new \SplObjectStorage();
    }

    public function addOne($entity)
    {
        $this->entities->attach($entity);
    }

    public function addMany(array $entities)
    {
        foreach ($entities as $entity) {
            $this->addOne($entity);
        }
    }

    public function getAll(): ArrayCollection
    {
        $all = new ArrayCollection();
        foreach ($this->entities as $entity) {
            $all->add($entity);
        }

        return $all;
    }
}
