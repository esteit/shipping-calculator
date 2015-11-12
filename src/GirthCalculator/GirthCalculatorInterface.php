<?php

namespace EsteIt\ShippingCalculator\GirthCalculator;

use EsteIt\ShippingCalculator\Model\DimensionsInterface;
use EsteIt\ShippingCalculator\Model\GirthInterface;

/**
 * Interface GirthCalculatorInterface
 */
interface GirthCalculatorInterface
{
    /**
     * @param DimensionsInterface $dimensions
     * @return GirthInterface
     */
    public function calculate(DimensionsInterface $dimensions);
}
