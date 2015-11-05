<?php

include_once __DIR__.'/../vendor/autoload.php';

$config = include __DIR__.'/../src/Resources/Asendia/PMI/index.php';

$factory = new \EsteIt\PackageDeliveryCalculator\Factory\AsendiaCalculatorFactory();
$deliveryMethod = $factory->create($config);

var_dump($deliveryMethod);