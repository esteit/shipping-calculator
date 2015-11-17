<?php

use EsteIt\ShippingCalculator\CalculatorHandler\AsendiaCalculatorHandler;;
use EsteIt\ShippingCalculator\CalculatorHandler\DhlCalculatorHandler;
use EsteIt\ShippingCalculator\Model\Weight;
use EsteIt\ShippingCalculator\Model\Dimensions;
use EsteIt\ShippingCalculator\Model\Address;
use EsteIt\ShippingCalculator\Model\Package;
use EsteIt\ShippingCalculator\Calculator\SelectiveCalculator;
use EsteIt\ShippingCalculator\Model\PackageInterface;
use EsteIt\ShippingCalculator\Calculator\CalculatorInterface;
use EsteIt\ShippingCalculator\CalculatorHandler\CalculatorHandlerInterface;

include_once __DIR__.'/../vendor/autoload.php';

$config1 = include __DIR__.'/../src/Resources/Asendia/PMI/tariff_2015_06_15.php';
$config2 = include __DIR__.'/../src/Resources/DHL/ExportExpressWorldWide/tariff_2015_08_25_usa.php';

// This \Closure will find actual handler by date from handler's extra data
$selector = function ($handlers, PackageInterface $package) {
    /** @var CalculatorInterface $currentHandler */
    $currentHandler = null;
    $currentDate = null;

    /** @var CalculatorHandlerInterface $handler */
    foreach ($handlers as $handler) {
        $extraData = $handler->get('extra_data');
        $date = new \DateTime($extraData['date']);
        if ($package->getCalculationDate() >= $date && (is_null($currentHandler) || $date > $currentDate)) {
            $currentHandler = $handler;
        }
    }
    return $currentHandler;
};

$calculator = new SelectiveCalculator([
    'handlers' => [
        AsendiaCalculatorHandler::create($config1),
        DhlCalculatorHandler::create($config2)
    ],
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

