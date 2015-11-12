<?php

namespace EsteIt\ShippingCalculator\Model;

/**
 * Class VolumetricWeight
 */
class VolumetricWeight implements VolumetricWeightInterface
{
    protected $value;
    protected $massUnit;
    protected $dimensionsUnit;

    /**
     * @param mixed $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $unit
     * @return $this
     */
    public function setMassUnit($unit)
    {
        $this->massUnit = $unit;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMassUnit()
    {
        return $this->massUnit;
    }

    /**
     * @param mixed $unit
     * @return $this
     */
    public function setDimensionsUnit($unit)
    {
        $this->dimensionsUnit = $unit;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDimensionsUnit()
    {
        return $this->dimensionsUnit;
    }
}
