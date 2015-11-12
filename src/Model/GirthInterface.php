<?php

namespace EsteIt\ShippingCalculator\Model;

/**
 * Interface GirthInterface
 */
interface GirthInterface
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
