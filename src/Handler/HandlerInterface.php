<?php

namespace EsteIt\ShippingCalculator\Handler;

use EsteIt\ShippingCalculator\Package;
use EsteIt\ShippingCalculator\Result;

interface HandlerInterface
{
    /**
     * @param string|int $name
     * @return mixed
     */
    public function get($name);

    /**
     * @param Result $result
     * @param Package $package
     * @return void
     */
    public function calculate(Result $result, Package $package);
}
