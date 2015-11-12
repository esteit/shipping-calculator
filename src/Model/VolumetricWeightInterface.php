<?php

namespace EsteIt\ShippingCalculator\Model;

/**
 * Interface VolumetricWeightInterface
 */
interface VolumetricWeightInterface
{
    /**
     * @return string
     */
    public function getValue();

    /**
     * @return string
     */
    public function getDimensionsUnit();

    /**
     * @return string
     */
    public function getMassUnit();
}
