<?php

namespace Malef\AssociateTests\Functional\DoctrineOrm\Mock\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity
 * @Table(name="baz")
 */
class Baz
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
     * @var Bar|null
     *
     * @OneToOne(targetEntity="Bar", mappedBy="baz")
     */
    protected $bar;

    /**
     * @var Collection|Foo[]
     *
     * @ManyToMany(targetEntity="Foo", mappedBy="bazs")
     */
    protected $foos;

    /**
     * @var Collection|Qux[]
     *
     * @ManyToMany(targetEntity="Qux", inversedBy="bazs")
     */
    protected $quxs;

    public function __construct()
    {
        $this->foos = new ArrayCollection();
        $this->quxs = new ArrayCollection();
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setPayload(string $payload): void
    {
        $this->payload = $payload;
    }

    public function getPayload(): string
    {
        return $this->payload;
    }

    public function setBar(?Bar $bar): void
    {
        $this->bar = $bar;
    }

    public function getBar(): ?Bar
    {
        return $this->bar;
    }

    /**
     * @return Collection|Foo[]
     */
    public function getFoos(): Collection
    {
        return $this->foos;
    }

    /**
     * @param Qux[] $quxs
     */
    public function setQuxs(array $quxs): void
    {
        $this->quxs->clear();
        foreach ($quxs as $qux) {
            $this->quxs->add($qux);
        }
    }

    /**
     * @return Collection|Qux[]
     */
    public function getQuxs(): Collection
    {
        return $this->quxs;
    }

    public function __toString(): string
    {
        return implode(':', [$this->id, $this->payload]);
    }

    public function toArrayDataSetItemsMap(): array
    {
        return [
            'baz' => [
                [
                    'id' => $this->id,
                    'payload' => $this->payload,
                ],
            ],
        ];
    }
}
