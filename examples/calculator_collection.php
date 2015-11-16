<?php

use EsteIt\ShippingCalculator\Calculator\AsendiaCalculator;
use EsteIt\ShippingCalculator\Model\Weight;
use EsteIt\ShippingCalculator\Model\Dimensions;
use EsteIt\ShippingCalculator\Model\Address;
use EsteIt\ShippingCalculator\Model\Package;
use EsteIt\ShippingCalculator\Collection\CalculatorCollection;

include_once __DIR__.'/../vendor/autoload.php';

$config1 = include __DIR__.'/../src/Resources/Asendia/PMI/tariff_2015_06_15.php';
$config2 = include __DIR__.'/../src/Resources/Asendia/PMEI/tariff_2015_06_15.php';

$collection = new CalculatorCollection([
    AsendiaCalculator::create($config1),
    AsendiaCalculator::create($config2)
]);

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
$package->setCalculationDate(new \DateTime());
$package->setWeight($weight);
$package->setDimensions($dimensions);
$package->setSenderAddress($senderAddress);
$package->setRecipientAddress($recipientAddress);

$results = $collection->calculate($package);

var_dump($results[0]->getTotalCost());
var_dump($results[1]->getTotalCost());

