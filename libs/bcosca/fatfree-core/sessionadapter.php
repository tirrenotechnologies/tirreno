<?php

/**
 * To be removed once the legacy session handlers are reworked when php 7 support is dropped
 */
class SessionAdapter implements \SessionHandlerInterface
{
    protected $_handler;

    public function __construct($handler)
    {
        $this->_handler = $handler;
    }

    public function close(): bool
    {
        return $this->_handler->close();
    }

    public function destroy(string $id): bool
    {
        return $this->_handler->destroy($id);
    }

    #[ReturnTypeWillChange]
    public function gc(int $max_lifetime): int
    {
        return $this->_handler->gc($max_lifetime);
    }

    public function open(string $path, string $name): bool
    {
        return $this->_handler->open($path, $name);
    }

    #[ReturnTypeWillChange]
    public function read(string $id): string
    {
        return $this->_handler->read($id);
    }

    public function write(string $id, string $data): bool
    {
        return $this->_handler->write($id, $data);
    }
}