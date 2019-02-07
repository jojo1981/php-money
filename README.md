PHP Money pattern
=================

Author: Laurens Hellemons <lhellemons@gmail.com>


Usage
-----

Install the package using composer.

```bash
composer require lhellemons/php-money
```

```php
<?php

use SolidPhp\Money\Money\Currency;
use SolidPhp\Money\Money\Money;

$price = Money::amountOf(100, Currency::EUR());
$vatPercentage = 0.30;
$priceWithVat = $price->times(1 + $vatPercentage);
$vatAmount = $priceWithVat->minus($price);

echo $price; // EUR 100
echo $priceWithVat; // EUR 130
echo $vatAmount; // EUR 30

echo $price->compareTo($priceWithVat); // -1
echo $price <=> $priceWithVat; // -1, be careful to use the same currency!

```
