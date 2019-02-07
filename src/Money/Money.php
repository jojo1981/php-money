<?php

namespace SolidPhp\Money\Money;

use SolidPhp\ValueObjects\Value\ValueObjectTrait;

class Money
{
    use ValueObjectTrait;

    /** @var int */
    private $fractionalUnitAmount;

    /** @var float */
    private $mainUnitAmount;

    /** @var Currency */
    private $currency;

    private function __construct(int $fractionalUnitAmount, Currency $currency)
    {
        $this->fractionalUnitAmount = $fractionalUnitAmount;
        $this->mainUnitAmount = $fractionalUnitAmount / $currency->getMainUnitQuantity();
        $this->currency = $currency;
    }

    public function getFractionalUnitAmount(): int
    {
        return $this->fractionalUnitAmount;
    }

    public function getAmount(): float
    {
        return $this->mainUnitAmount;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public static function amountOf(float $amount, Currency $currency): self
    {
        return self::getInstance((int)($amount * $currency->getMainUnitQuantity()), $currency);
    }

    public static function amountOfFractionalUnits(int $amount, Currency $currency): self
    {
        return self::amountOf($amount / $currency->getMainUnitQuantity(), $currency);
    }

    public static function zeroOf(Currency $currency): self
    {
        return self::getInstance(0, $currency);
    }

    public function withAmount(int $amount): self
    {
        return self::getInstance($amount, $this->currency);
    }

    public function plus(Money $money): self
    {
        self::assertSameCurrencies($this, $money);

        return self::amountOf($this->fractionalUnitAmount + $money->fractionalUnitAmount, $this->currency);
    }

    public function minus(Money $money): self
    {
        self::assertSameCurrencies($this, $money);

        return self::amountOf($this->fractionalUnitAmount - $money->fractionalUnitAmount, $this->currency);
    }

    public function times(float $multiplier): self
    {
        return self::amountOf($this->fractionalUnitAmount * $multiplier, $this->currency);
    }

    public function numberOfMainUnits(): float
    {
        return $this->fractionalUnitAmount / $this->currency->getMainUnitQuantity();
    }

    public function compareTo(Money $money): int
    {
        self::assertSameCurrencies($this, $money);

        return $this->fractionalUnitAmount <=> $money->fractionalUnitAmount;
    }

    /**
     * @param float[]|int $portions
     *
     * @return MoneyArray
     */
    public function allocate($portions): MoneyArray
    {
        if (is_numeric($portions)) {
            $portions = array_fill(0,$portions, 1);
        }

        if (!is_array($portions)) {
            throw new \InvalidArgumentException(
                sprintf(
                    '$portions must be either an array of portion sizes or an int specifying the number of equal portions. The value passed was %s',
                    print_r($portions, true)
                )
            );
        }

        rsort($portions, SORT_NUMERIC & SORT_REGULAR);

        $moneyPortions = new MoneyArray();

        $total = array_sum($portions);

        $moneyAllocated = $this->withAmount(0);

        foreach ($portions as $key => $portion) {
            $moneyPortions[$key] = $portionMoney = $this->times($portion)->times(1 / $total);
            $moneyAllocated = $moneyAllocated->plus($portionMoney);
        }

        $remainingMoney = $this->minus($moneyAllocated);

        $keys = array_keys($portions);
        $unitAmount = $this->withAmount(1);
        $i = 0;

        while ($remainingMoney->fractionalUnitAmount > 0) {
            $key = $keys[$i];
            $moneyPortion = $moneyPortions->offsetGet($key) ?: $this->withAmount(0);
            $moneyPortions[$key] = $moneyPortion->plus($unitAmount);
            $i = ($i + 1) % count($keys);
        }

        return $moneyPortions;
    }

    private static function assertSameCurrencies(Money $a, Money $b): void
    {
        if ($a->currency !== $b->currency) {
            throw new \DomainException(sprintf('Currencies %s and %s do not match', $a->currency, $b->currency));
        }
    }

    public function __toString()
    {
        return sprintf('%s %d', $this->currency, $this->fractionalUnitAmount);
    }
}
