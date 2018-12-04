<?php

namespace Malef\AssociateTests\Functional\DoctrineOrm\Mock\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity
 * @Table(name="bar")
 */
class Bar
{
    /**
     * @var string
     *
     * @Id
     * @Column(type="string", length=64)
     */
    protected $id;

    /**
     * @var string
     *
     * @Column(type="string", length=128)
     */
    protected $payload;

    /**
     * @var Collection|Foo[]
     *
     * @OneToMany(targetEntity="Foo", mappedBy="bar")
     */
    protected $foos;

    /**
     * @var Baz|null
     *
     * @OneToOne(targetEntity="Baz", inversedBy="bar")
     */
    protected $baz;

    /**
     * Bar constructor.
     */
    public function __construct()
    {
        $this->foos = new ArrayCollection();
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $payload
     */
    public function setPayload(string $payload): void
    {
        $this->payload = $payload;
    }

    /**
     * @return string
     */
    public function getPayload(): string
    {
        return $this->payload;
    }

    /**
     * @return Collection|Foo[]
     */
    public function getFoos(): Collection
    {
        return $this->foos;
    }

    /**
     * @param Baz|null $baz
     */
    public function setBaz(?Baz $baz): void
    {
        $this->baz = $baz;
    }

    /**
     * @return Baz|null
     */
    public function getBaz(): ?Baz
    {
        return $this->baz;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return implode(':', [$this->id, $this->payload]);
    }

    /**
     * @return array
     */
    public function toArrayDataSetItemsMap(): array
    {
        return [
            'bar' => [
                [
                    'id' => $this->id,
                    'payload' => $this->payload,
                    'baz_id' => $this->baz instanceof Baz
                        ? $this->baz->getId()
                        : null,
                ],
            ],
        ];
    }
}
