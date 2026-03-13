<?php

namespace Cooper\CanalClient\Tests;

use Cooper\CanalClient\Message;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    public function test_default_id_is_zero(): void
    {
        $message = new Message();
        $this->assertSame(0, $message->getId());
    }

    public function test_set_and_get_id(): void
    {
        $message = new Message();
        $message->setId(42);
        $this->assertSame(42, $message->getId());
    }

    public function test_default_entries_is_empty(): void
    {
        $message = new Message();
        $this->assertSame([], $message->getEntries());
    }
}
