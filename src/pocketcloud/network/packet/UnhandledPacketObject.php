<?php

namespace pocketcloud\network\packet;

use pmmp\thread\ThreadSafe;

class UnhandledPacketObject extends ThreadSafe {

    public function __construct(
        private string $buffer,
        private string $address,
        private int $port
    ) {}

    public function getBuffer(): string {
        return $this->buffer;
    }

    public function getAddress(): string {
        return $this->address;
    }

    public function getPort(): int {
        return $this->port;
    }
}