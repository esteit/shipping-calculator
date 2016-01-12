<?php

namespace EsteIt\ShippingCalculator\Model;

/**
 * Interface LengthInterface
 */
interface LengthInterface
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
