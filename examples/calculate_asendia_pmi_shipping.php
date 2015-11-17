<?php

use EsteIt\ShippingCalculator\Calculator\BaseCalculator;
use EsteIt\ShippingCalculator\CalculatorHandler\AsendiaCalculatorHandler;
use EsteIt\ShippingCalculator\Model\Weight;
use EsteIt\ShippingCalculator\Model\Dimensions;
use EsteIt\ShippingCalculator\Model\Address;
use EsteIt\ShippingCalculator\Model\Package;

include_once __DIR__.'/../vendor/autoload.php';

$config = include __DIR__.'/../src/Resources/Asendia/PMEI/tariff_2015_06_15.php';

$calculator = new BaseCalculator([
    'handler' => AsendiaCalculatorHandler::create($config)
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

$result = $calculator->calculate($package);

var_dump($result->getTotalCost());

