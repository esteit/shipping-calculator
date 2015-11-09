<?php

namespace EsteIt\ShippingCalculator\Model;

/**
 * Interface WeightInterface
 */
interface WeightInterface
{
    /**
     * @return string
     */
    public function getValue();

    /**
     * @return string
     */
    public function getUnit();
}
