<?php

namespace Cooper\CanalClient;

use Com\Alibaba\Otter\Canal\Protocol\Entry;

class Message
{
    private int $id = 0;

    /** @var Entry[] */
    private array $entries = [];

    public function getId(): int
    {
        return $this->id;
    }

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

    public function addEntries(Entry $entry): void
    {
        $this->entries[] = $entry;
    }
}
