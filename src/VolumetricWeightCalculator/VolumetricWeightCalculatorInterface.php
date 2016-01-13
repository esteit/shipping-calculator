<?php

namespace EsteIt\ShippingCalculator\VolumetricWeightCalculator;

use EsteIt\ShippingCalculator\Model\DimensionsInterface;
use EsteIt\ShippingCalculator\Model\WeightInterface;

interface VolumetricWeightCalculatorInterface
{
    /**
     * @param DimensionsInterface $dimensions
     * @return WeightInterface
     */
    public function calculate(DimensionsInterface $dimensions);
}
