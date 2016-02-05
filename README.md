# Shipping Calculator

[![Build Status](https://travis-ci.org/esteit/shipping-calculator.svg?branch=master)](https://travis-ci.org/esteit/shipping-calculator)
[![Coverage Status](https://coveralls.io/repos/esteit/shipping-calculator/badge.svg?branch=master&service=github)](https://coveralls.io/github/esteit/shipping-calculator?branch=master)
[![Code Climate](https://codeclimate.com/github/esteit/shipping-calculator/badges/gpa.svg)](https://codeclimate.com/github/esteit/shipping-calculator)
[![Latest Stable Version](https://poser.pugx.org/shiptor/shipping-calculator/v/stable)](https://packagist.org/packages/shiptor/shipping-calculator)
[![Total Downloads](https://poser.pugx.org/shiptor/shipping-calculator/downloads)](https://packagist.org/packages/shiptor/shipping-calculator)
[![License](https://poser.pugx.org/shiptor/shipping-calculator/license)](https://packagist.org/packages/shiptor/shipping-calculator)

Shipping calculation library based on Symfony 2 components.

## Installation

Add in your ```composer.json``` the require entry for this library.
```json
{
    "require": {
        "shiptor/shipping-calculator": "*"
    }
}
```
and run ```composer install``` (or ```update```) to download all files.

## Usage

### Base calculator creation

Example code below will create the calculator for a single shipment method.

```php
$config = include __DIR__.'/../src/Resources/DHL/ExportExpressWorldWide/tariff_2015_08_25_usa.php';
$calculator = new BaseCalculator([
    'handler' => DhlCalculatorHandler::create($config)
]);
```

What is what:
- [DhlCalculatorHandler](/src/Calculator/BaseCalculator.php) contains calculation algorithm for the Dhl Express Shipping Method
- [$config](/src/Resources/DHL/ExportExpressWorldWide/tariff_2015_08_25_usa.php) contains configuration for the `DhlCalculatorHandler`
- [BaseCalculator](/src/Calculator/BaseCalculator.php) is a wrapper for a calculation handlers, it contains an algorithm "How to use calculation handlers" and returns a calculation result

