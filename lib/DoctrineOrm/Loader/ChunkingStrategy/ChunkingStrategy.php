<?php

namespace Malef\Associate\DoctrineOrm\Loader\ChunkingStrategy;

use Doctrine\Common\Collections\ArrayCollection;

class ChunkingStrategy
{
    const DEFAULT_CHUNK_SIZE = 1000;

    /**
     * @var int
     */
    protected $chunkSize;

    public function __construct(int $chunkSize = self::DEFAULT_CHUNK_SIZE)
    {
        $this->chunkSize = $chunkSize;
    }

    /**
     * @return ArrayCollection[]
     */
    public function chunk(ArrayCollection $entities): array
    {
        $chunks = [];
        $entitiesCount = count($entities);
        for ($offset = 0; $offset < $entitiesCount; $offset += $this->chunkSize) {
            $chunks[] = new ArrayCollection($entities->slice($offset, $this->chunkSize));
        }

        return $chunks;
    }
}
