<?php

namespace EsteIt\ShippingCalculator\Handler;

use EsteIt\ShippingCalculator\Package;
use EsteIt\ShippingCalculator\Result;

interface ValidationHandlerInterface
{
    /**
     * @param Result $result
     * @param Package $package
     * @return void
     */
    public function validate(Result $result, Package $package);
}
