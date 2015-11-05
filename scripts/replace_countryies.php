<?php

$filename = './Resources/Asendia/PMI/countries.php';
$countries = include './Resources/countries.php';
$content = file_get_contents($filename);

foreach ($countries as $code => $country) {
    $content = str_replace($country, $code, $content);
}

file_put_contents($filename, $content);
echo $content;