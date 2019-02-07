<?php

namespace SolidPhp\Money\Money;

use ArrayAccess;
use ArrayIterator;
use DomainException;
use IteratorAggregate;

class MoneyArray implements ArrayAccess, IteratorAggregate
{
    /** @var Money[] */
    private $elements = [];

    /**
     * @param Money[] $initialElements
     */
    public function __construct(array $initialElements = [])
    {
        foreach ($initialElements as $offset => $value) {
            $this->offsetSet($offset, $value);
        }
    }

    public function getIterator()
    {
        return new ArrayIterator($this->elements);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->elements[$offset]);
    }

    public function offsetGet($offset): ?Money
    {
        return $this->elements[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        if (!$value instanceof Money) {
            throw new DomainException(sprintf('%s can only contain instances of %s', static::class, Money::class));
        }

        $this->elements[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->elements[$offset]);
    }
}
