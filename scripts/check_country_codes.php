<?php


$countries = include './Resources/countries.php';
$target = include './Resources/Asendia/PMI/countries.php';

foreach ($target as $code => $country) {
    if (!array_key_exists($code, $countries)) {
        echo sprintf('Invalid country code: %s\n', $code);
    }
}
