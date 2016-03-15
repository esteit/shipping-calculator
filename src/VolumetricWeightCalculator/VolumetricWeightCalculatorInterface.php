<?php

namespace EsteIt\ShippingCalculator\VolumetricWeightCalculator;

use EsteIt\ShippingCalculator\Dimensions;
use EsteIt\ShippingCalculator\Weight;

interface VolumetricWeightCalculatorInterface
{
    /**
     * @param Dimensions $dimensions
     * @return Weight
     */
    public function calculate(Dimensions $dimensions);
}
