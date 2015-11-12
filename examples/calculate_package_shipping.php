<?php

include_once __DIR__.'/../vendor/autoload.php';

$config = include __DIR__.'/../src/Resources/Asendia/PMI/index.php';

$factory = new \EsteIt\ShippingCalculator\Factory\AsendiaCalculatorFactory();
$calculator = $factory->create($config);

$weight = new \EsteIt\ShippingCalculator\Model\Weight();
$weight->setValue(10);
$weight->setUnit('lb');

$dimensions = new \EsteIt\ShippingCalculator\Model\Dimensions();
$dimensions->setLength(10);
$dimensions->setWidth(10);
$dimensions->setHeight(10);
$dimensions->setUnit('in');

$senderAddress = new \EsteIt\ShippingCalculator\Model\Address();
$senderAddress->setCountryCode('USA');

$recipientAddress = new \EsteIt\ShippingCalculator\Model\Address();
$recipientAddress->setCountryCode('RUS');

$package = new \EsteIt\ShippingCalculator\Model\Package();
$package->setCalculationDate(new \DateTime());
$package->setWeight($weight);
$package->setDimensions($dimensions);
$package->setSenderAddress($senderAddress);
$package->setRecipientAddress($recipientAddress);

$result = $calculator->calculate($package);

var_dump($result->getTotalCost());

