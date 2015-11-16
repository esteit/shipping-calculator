<?php

namespace EsteIt\ShippingCalculator\VolumetricWeightCalculator;

use EsteIt\ShippingCalculator\Model\DimensionsInterface;
use EsteIt\ShippingCalculator\Model\WeightInterface;

/**
 * Class VolumetricWeightCalculatorInterface
 */
interface VolumetricWeightCalculatorInterface
{
    /**
     * @param DimensionsInterface $dimensions
     * @param string $toWeightUnit
     * @return WeightInterface
     */
    public function calculate(DimensionsInterface $dimensions, $toWeightUnit);
}
