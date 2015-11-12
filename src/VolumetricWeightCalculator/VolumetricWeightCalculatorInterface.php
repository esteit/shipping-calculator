<?php

namespace EsteIt\ShippingCalculator\VolumetricWeightCalculator;

use EsteIt\ShippingCalculator\Model\DimensionsInterface;
use EsteIt\ShippingCalculator\Model\VolumetricWeightInterface;

/**
 * Class VolumetricWeightCalculatorInterface
 */
interface VolumetricWeightCalculatorInterface
{
    /**
     * @param DimensionsInterface $dimensions
     * @param string $toWeightUnit
     * @return VolumetricWeightInterface
     */
    public function calculate(DimensionsInterface $dimensions, $toWeightUnit);
}
