<?php

namespace Cooper\CanalClient;

use Com\Alibaba\Otter\Canal\Protocol\Entry;

class Message
{
    /** @var int */
    private int $id = 0;

    /** @var Entry[] */
    private array $entries = [];

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return void
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return Entry[]
     */
    public function getEntries(): array
    {
        return $this->entries;
    }

    /**
     * @param Entry $entry
     * @return void
     */
    public function addEntries(Entry $entry): void
    {
        $this->entries[] = $entry;
    }
}