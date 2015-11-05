<?php

include_once __DIR__.'/../vendor/autoload.php';

$config = include __DIR__.'/../src/Resources/Asendia/PMI/index.php';

$factory = new \Rage\PackageDeliveryCalculator\Factory\AsendiaDeliveryMethodFactory();
$deliveryMethod = $factory->create($config);

var_dump($deliveryMethod);