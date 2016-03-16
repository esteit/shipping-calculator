<?php

use EsteIt\ShippingCalculator\Calculator\BaseCalculator;
use EsteIt\ShippingCalculator\Handler\IParcelHandler;
use EsteIt\ShippingCalculator\Weight;
use EsteIt\ShippingCalculator\Dimensions;
use EsteIt\ShippingCalculator\Address;
use EsteIt\ShippingCalculator\Package;

include_once __DIR__.'/../vendor/autoload.php';

$config = include __DIR__.'/../src/Resources/IParcel/tariff_2015_01_12_usa.php';

$calculator = new BaseCalculator([
    'handler' => IParcelHandler::create($config)
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
$recipientAddress->setCountryCode('SGP');

$package = new Package();
$package->setWeight($weight);
$package->setDimensions($dimensions);
$package->setSenderAddress($senderAddress);
$package->setRecipientAddress($recipientAddress);

$result = $calculator->calculate($package);

var_dump($result->getData());

