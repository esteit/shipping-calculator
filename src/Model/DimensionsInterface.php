<?php

namespace EsteIt\ShippingCalculator\Model;

/**
 * Interface DimensionsInterface
 */
interface DimensionsInterface
{
    /**
     * @return string
     */
    public function getLength();

    /**
     * @return string
     */
    public function getWidth();

    /**
     * @return string
     */
    public function getHeight();

    /**
     * @return string
     */
    public function getUnit();
}
