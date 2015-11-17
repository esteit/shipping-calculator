<?php

use EsteIt\ShippingCalculator\Calculator\AsendiaCalculator;
use EsteIt\ShippingCalculator\Model\Weight;
use EsteIt\ShippingCalculator\Model\Dimensions;
use EsteIt\ShippingCalculator\Model\Address;
use EsteIt\ShippingCalculator\Model\Package;
use EsteIt\ShippingCalculator\Collection\CalculatorCollection;
use EsteIt\ShippingCalculator\Calculator\SelectiveCalculator;
use EsteIt\ShippingCalculator\Calculator\DhlCalculator;
use EsteIt\ShippingCalculator\Model\PackageInterface;
use EsteIt\ShippingCalculator\Calculator\CalculatorInterface;

include_once __DIR__.'/../vendor/autoload.php';

$config1 = include __DIR__.'/../src/Resources/Asendia/PMI/tariff_2015_06_15.php';
$config2 = include __DIR__.'/../src/Resources/DHL/ExportExpressWorldWide/tariff_2015_08_25_usa.php';

$collection = new CalculatorCollection([
    AsendiaCalculator::create($config1),
    DhlCalculator::create($config2)
]);

// This \Closure will find actual calculator by date from `extra_data`
$selector = function (CalculatorCollection $calculators, PackageInterface $package) {
    /** @var CalculatorInterface $currentCalculator */
    $currentCalculator = null;
    $currentDate = null;

    /** @var CalculatorInterface $calculator */
    foreach ($calculators as $calculator) {
        $extraData = $calculator->getExtraData();
        $date = new \DateTime($extraData['date']);
        if ($package->getCalculationDate() >= $date && (is_null($currentCalculator) || $date > $currentDate)) {
            $currentCalculator = $calculator;
        }
    }
    return $currentCalculator;
};

$calculator = new SelectiveCalculator([
    'calculators' => $collection,
    'selector' => $selector
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
// Change this date to calculate shipping by other calculator.
// For example, `2015-12-12`.
$package->setCalculationDate(new \DateTime('2015-06-20'));
$package->setWeight($weight);
$package->setDimensions($dimensions);
$package->setSenderAddress($senderAddress);
$package->setRecipientAddress($recipientAddress);

$result = $calculator->calculate($package);

var_dump($result->getTotalCost());

