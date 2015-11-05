<?php

include_once __DIR__.'/../vendor/autoload.php';

$config = include __DIR__.'/../src/Resources/Asendia/PMI/index.php';

$factory = new \EsteIt\ShippingCalculator\Factory\AsendiaCalculatorFactory();
$calculator = $factory->create($config);

var_dump($calculator);