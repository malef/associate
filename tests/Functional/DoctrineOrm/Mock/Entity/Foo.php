<?php

namespace Malef\AssociateTests\Functional\DoctrineOrm\Mock\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\ManyToMany;

/**
 * @Entity
 * @Table(name="foo")
 */
class Foo
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
     * @ManyToOne(targetEntity="Bar", inversedBy="foos")
     */
    protected $bar;

    /**
     * @var Collection|Baz[]
     *
     * @ManyToMany(targetEntity="Baz", inversedBy="foos")
     */
    protected $bazs;

    /**
     * @var Collection|Qux[]
     *
     * @ManyToMany(targetEntity="Qux", inversedBy="foos")
     */
    protected $quxs;

    public function __construct()
    {
        $this->bazs = new ArrayCollection();
        $this->quxs = new ArrayCollection();
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

    public function setBar(?Bar $bar): void
    {
        $this->bar = $bar;
    }

    /**
     * @return Bar|null
     */
    public function getBar(): ?Bar
    {
        return $this->bar;
    }

    /**
     * @param Baz[] $bazs
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
            'foo' => [
                [
                    'id' => $this->id,
                    'payload' => $this->payload,
                    'bar_id' => $this->bar instanceof Bar
                        ? $this->bar->getId()
                        : null,
                ],
            ],
            'foo_baz' => array_map(
                function (Baz $baz) {
                    return [
                        'foo_id' => $this->id,
                        'baz_id' => $baz->getId(),
                    ];
                },
                $this->bazs->getValues()
            ),
            'foo_qux' => array_map(
                function (Qux $qux) {
                    return [
                        'foo_id' => $this->id,
                        'qux_id' => $qux->getId(),
                    ];
                },
                $this->quxs->getValues()
            ),
        ];
    }
}
