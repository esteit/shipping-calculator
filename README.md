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

### How to create a calculator?

Example code below will create the calculator for a single shipment method.

```php
$config = include __DIR__.'/../src/Resources/DHL/ExportExpressWorldWide/tariff_2015_08_25_usa.php';
$calculator = new BaseCalculator([
    'handler' => DhlCalculatorHandler::create($config)
]);
```

What is what:
- [DhlCalculatorHandler](/src/Calculator/BaseCalculator.php) contains calculation algorithm for the Dhl Express Shipping Method;
- [$config](/src/Resources/DHL/ExportExpressWorldWide/tariff_2015_08_25_usa.php) contains configuration for the `DhlCalculatorHandler`;
- [BaseCalculator](/src/Calculator/BaseCalculator.php) is a wrapper for a calculation handlers, it contains an algorithm "How to use calculation handlers" and returns a calculation result;

### How to calculate a package shipping?

Example code below will create a package and calculate shipping cost for Dhl Express.

```php
// previous example code here

$weight = new Weight();
$weight->setValue(10);
$weight->setUnit('lb');

$dimensions = new Dimensions();
$dimensions->setLength(10);
$dimensions->setWidth(10);
$dimensions->setHeight(10);
$dimensions->setUnit('in');

$senderAddress = new Address();
$senderAddress->setCountryCode('USA');

$recipientAddress = new Address();
$recipientAddress->setCountryCode('RUS');

$package = new Package();
$package->setWeight($weight);
$package->setDimensions($dimensions);
$package->setSenderAddress($senderAddress);
$package->setRecipientAddress($recipientAddress);

$result = $calculator->calculate($package);
```

What is what:
- [Weight](/src/Model/Weight.php) contains information about physical weight;
- [Dimensions](/src/Model/Dimensions.php) contains information about package box dimensions. It is required to calculate a volumetric weight of your package;
- [$senderAddress](/src/Model/Address.php) and [$recipientAddress](/src/Model/Address.php) contains information about sender and recipient;
- [Package](/src/Model/Package.php) is a wrapper object to all objects above. You will need to pass this object to `calculate` method of your calculator;
- [$result](/src/Model/CalculationResult.php) contains your package and resulting calculation data;


### How to extend a calculator?

Shipping calculator uses [symfony event dispatcher](https://github.com/symfony/event-dispatcher) and you can use it to extend calculation algorithms as you need. For example, you can increase shipping cost by 10$.

```php
// place calculator creation code here

$calculator->getDispatcher()->addListener(Events::AFTER_CALCULATE, function (AfterCalculateEvent $event) {
    $event->getResult()->setShippingCost($event->getResult()->getShippingCost() + 10);
});
```

What is what:
- `Events::AFTER_CALCULATE` is an event calling when calculation ends and calculation result is ready;
- `AfterCalculateEvent` is an event object which contains calculation result and package. Look to other available events [here](/src/Event);

### More ideas how to use and extend shipping calculator

- create calculation handlers for other couriers and shipping methods;
- create calculators and realize your own algorithms using handlers;
- extend [models](/src/Model). For example i use it with [Doctrine ORM](https://github.com/doctrine/doctrine2) entities;
