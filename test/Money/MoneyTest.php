<?php

namespace Test\SolidPhp\Money\Money;

use PHPUnit\Framework\TestCase;
use SolidPhp\Money\Money\Currency;
use SolidPhp\Money\Money\Money;
use SolidPhp\Money\Money\MoneyArray;

class MoneyTest extends TestCase
{
    const TEST_AMOUNTS = [
        '0'        => 0,
        '1'        => 1,
        '10'       => 10,
        '100'      => 100,
        '-1'       => -1,
        '-10'      => -10,
        '-100'     => -100,
        '100 / 3'  => 100 / 3,
        '-100 / 3' => -100 / 3,
    ];

    /**
     * @dataProvider casesAmountOf
     *
     * @param float    $amount
     * @param Currency $currency
     * @param float    $expectedAmount
     */
    public function testAmountOf(float $amount, Currency $currency, float $expectedAmount): void
    {
        $money = Money::amountOf($amount, $currency);

        $this->assertEquals($expectedAmount, $money->getAmount());
        $this->assertSame($currency, $money->getCurrency());
    }

    public function casesAmountOf(): array
    {
        return [
            'EUR 1'      => [1, Currency::EUR(), 1],
            'EUR 10'     => [10, Currency::EUR(), 10],
            'EUR -1'     => [-1, Currency::EUR(), -1],
            'EUR 100/3'  => [100 / 3, Currency::EUR(), 33.33],
            'EUR -100/3' => [-100 / 3, Currency::EUR(), -33.33],
        ];
    }

    /**
     * @dataProvider casesGetAmount
     *
     * @param Money $money
     * @param float $expectedAmount
     */
    public function testGetAmount(Money $money, float $expectedAmount): void
    {
        $this->assertEquals($expectedAmount, $money->getAmount());
    }

    public function casesGetAmount(): array
    {
        return [
            'EUR 1'      => [Money::amountOf(1, Currency::EUR()), 1],
            'EUR 10'     => [Money::amountOf(10, Currency::EUR()), 10],
            'EUR -1'     => [Money::amountOf(-1, Currency::EUR()), -1],
            'EUR 100/3'  => [Money::amountOf(100 / 3, Currency::EUR()), 33.33],
            'EUR -100/3' => [Money::amountOf(-100 / 3, Currency::EUR()), -33.33],
        ];
    }

    /**
     * @dataProvider casesGetCurrency
     *
     * @param Money    $money
     * @param Currency $expectedCurrency
     */
    public function testGetCurrency(Money $money, Currency $expectedCurrency): void
    {
        $this->assertSame($money->getCurrency(), $expectedCurrency);
    }

    public function casesGetCurrency(): array
    {
        return [
            'EUR 1'    => [Money::amountOf(1, Currency::EUR()), Currency::EUR()],
            'USD 1'    => [Money::amountOf(10, Currency::USD()), Currency::USD()],
            'custom 1' => [Money::amountOf(-1, Currency::of('custom', 100)), Currency::of('custom', 1000)],
        ];
    }

    /**
     * @dataProvider casesWithAmount
     *
     * @param Money $money
     * @param int   $amount
     * @param Money $expectedMoney
     */
    public function testWithAmount(Money $money, int $amount, Money $expectedMoney): void
    {
        $this->assertSame($expectedMoney, $money->withAmount($amount));
    }

    public function casesWithAmount(): array
    {
        return [
            '1 USD -> 2' => [Money::amountOf(1, Currency::USD()), 2, Money::amountOf(2, Currency::USD())],
            '10 EUR -> 0' => [Money::amountOf(10, Currency::EUR()), 0, Money::amountOf(0, Currency::EUR())],
            '-3 custom -> 0 custom' => [Money::amountOf(-3, Currency::of('custom')), 0, Money::amountOf(0, Currency::of('custom'))],
        ];
    }

    /**
     * @dataProvider casesNumberOfMainUnits
     *
     * @param Money $money
     * @param float $expectedResult
     */
    public function testNumberOfMainUnits(Money $money, float $expectedResult): void
    {
        $this->assertEquals($expectedResult, $money->numberOfMainUnits());
    }

    public function casesNumberOfMainUnits(): array
    {
        $hundredsCurrency = Currency::of('hundreds', 100);
        $thousandsCurrency = Currency::of('thousands', 1000);

        return [
            '1 1/100th' => [$hundredsCurrency->amountOfFractionalUnits(1), 0.01],
            '1 1/1000th' => [$thousandsCurrency->amountOfFractionalUnits(1), 0.001],
            '250 1/100ths' => [$hundredsCurrency->amountOfFractionalUnits(250), 2.5],
        ];
    }

    /**
     * @dataProvider casesZeroOf
     *
     * @param Currency $currency
     * @param Money    $expectedResult
     */
    public function testZeroOf(Currency $currency, Money $expectedResult): void
    {
        $this->assertSame($expectedResult, Money::zeroOf($currency));
    }

    public function casesZeroOf(): array
    {
        return [
            'EUR' => [Currency::EUR(), Money::amountOf(0,Currency::EUR())],
            'USD' => [Currency::USD(), Money::amountOf(0,Currency::USD())],
            'custom' => [Currency::of('custom'), Money::amountOf(0,Currency::of('custom'))],
        ];
    }

    /**
     * @dataProvider casesCompareTo
     *
     * @param Money $a
     * @param Money $b
     * @param int   $expectedResult
     */
    public function testCompareTo(Money $a, Money $b, ?int $expectedResult): void
    {
        if (null !== $expectedResult) {
            $this->assertEquals($expectedResult, $a->compareTo($b));
        } else {
            $this->expectException(\DomainException::class);
            $a->compareTo($b);
        }
    }

    public function casesCompareTo(): array
    {
        return [
            '1 EUR === 1 EUR' => [Money::amountOf(1, Currency::EUR()), Money::amountOf(1, Currency::EUR()), 0],
            '1 EUR < 2 EUR' => [Money::amountOf(1, Currency::EUR()), Money::amountOf(2, Currency::EUR()), -1],
            '-3 EUR > -10 EUR' => [Money::amountOf(-3, Currency::EUR()), Money::amountOf(-10, Currency::EUR()), 1],
            '1 EUR ### 1 USD' => [Money::amountOf(1, Currency::EUR()), Money::amountOf(1, Currency::USD()), null],
        ];
    }

    /**
     * @dataProvider casesPlus
     *
     * @param Money $a
     * @param Money $b
     * @param Money $expectedResult
     */
    public function testPlus(Money $a, Money $b, Money $expectedResult): void
    {
        $this->assertSame($expectedResult, $a->plus($b));
    }

    public function casesPlus(): array
    {
        return [
            'EUR 1 + EUR 1 === EUR 2' => [Money::amountOf(1, Currency::EUR()), Money::amountOf(1, Currency::EUR()), Money::amountOf(2, Currency::EUR())],
            'EUR 1 + EUR 10 === EUR 11' => [Money::amountOf(1, Currency::EUR()), Money::amountOf(10, Currency::EUR()), Money::amountOf(11, Currency::EUR())],
            'EUR 1 + EUR 0 === EUR 1' => [Money::amountOf(1, Currency::EUR()), Money::amountOf(0, Currency::EUR()), Money::amountOf(1, Currency::EUR())],
            'EUR 33.33 + EUR 66.67 === EUR 100' => [Money::amountOf(3333, Currency::EUR()), Money::amountOf(6667, Currency::EUR()), Money::amountOf(10000, Currency::EUR())],
        ];
    }

    /**
     * @dataProvider casesMinus
     *
     * @param Money $a
     * @param Money $b
     * @param Money $expectedResult
     */
    public function testMinus(Money $a, Money $b, Money $expectedResult): void
    {
        $this->assertSame($expectedResult, $a->minus($b));
    }

    public function casesMinus(): array
    {
        return [];
    }

    /**
     * @dataProvider casesTimes
     *
     * @param Money $a
     * @param float $multiplier
     * @param Money $expectedResult
     */
    public function testTimes(Money $a, float $multiplier, Money $expectedResult): void
    {
        $this->assertSame($expectedResult, $a->times($multiplier));
    }

    public function casesTimes(): array
    {
        return [];
    }

    /**
     * @dataProvider casesAllocate
     *
     * @param Money      $totalMoney
     * @param            $portions
     * @param MoneyArray $expectedResult
     */
    public function testAllocate(Money $totalMoney, $portions, MoneyArray $expectedResult): void
    {
        $this->assertEquals($expectedResult, $totalMoney->allocate($portions));
    }

    public function casesAllocate(): array
    {
        return [];
    }
}
