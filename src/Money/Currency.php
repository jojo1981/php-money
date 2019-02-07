<?php

namespace SolidPhp\Money\Money;

use SolidPhp\ValueObjects\Enum\EnumTrait;
use SolidPhp\ValueObjects\Value\ValueObjectTrait;

final class Currency
{
    use EnumTrait;

    /** @var string */
    private $symbol;

    /** @var int */
    private $mainUnitQuantity;

    public function __construct(string $symbol, int $mainUnitQuantity)
    {
        $this->symbol = $symbol;
        $this->mainUnitQuantity = $mainUnitQuantity;
    }

    public static function of(string $symbol, int $mainUnitQuantity = 100): self
    {
        return self::instance($symbol) ?: self::define($symbol, $mainUnitQuantity);
    }

    public static function EUR(): self
    {
        return self::define('EUR', 100);
    }

    public static function USD(): self
    {
        return self::define('USD', 100);
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getMainUnitQuantity(): int
    {
        return $this->mainUnitQuantity;
    }

    public function amountOfMainUnits(float $numberOfMainUnits = 0): Money
    {
        return Money::amountOf($this->getMainUnitQuantity(), $this)->times($numberOfMainUnits);
    }

    public function amountOfFractionalUnits(float $numberOfFractionalUnits = 0): Money
    {
        return Money::amountOf($numberOfFractionalUnits, $this);
    }

    public function zero(): Money
    {
        return Money::zeroOf($this);
    }

    public function __toString()
    {
        return $this->getId();
    }
}
