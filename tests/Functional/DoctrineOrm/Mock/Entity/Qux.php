<?php

namespace Malef\AssociateTests\Functional\DoctrineOrm\Mock\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\ManyToMany;

/**
 * @Entity
 * @Table(name="qux")
 */
class Qux
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
     * @ManyToMany(targetEntity="Foo", mappedBy="quxs")
     */
    protected $foos;

    /**
     * @var Collection|Baz[]
     *
     * @ManyToMany(targetEntity="Baz", mappedBy="quxs")
     */
    protected $bazs;

    public function __construct()
    {
        $this->foos = new ArrayCollection();
        $this->bazs = new ArrayCollection();
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
     * @param Foo $foo
     */
    public function setFoos(array $foos): void
    {
        $this->foos->clear();
        foreach ($foos as $foo) {
            $this->foos->add($foo);
        }
    }

    /**
     * @return Collection|Foo[]
     */
    public function getFoos(): Collection
    {
        return $this->foos;
    }

    /**
     * @param Baz $baz
     */
    public function setBazs(array $bazs): void
    {
        $this->bazs->clear();
        foreach ($bazs as $baz) {
            $this->bazs->add($baz);
        }
    }

    /**
     * @return Collection|Baz[]
     */
    public function getBazs(): Collection
    {
        return $this->bazs;
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
            'qux' => [
                [
                    'id' => $this->id,
                    'payload' => $this->payload,
                ],
            ],
        ];
    }
}
