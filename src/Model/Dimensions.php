<?php

namespace EsteIt\ShippingCalculator\Model;

class Dimensions implements DimensionsInterface
{
    protected $length;
    protected $width;
    protected $height;
    protected $unit;

    /**
     * @param mixed $value
     * @return $this
     */
    public function setLength($value)
    {
        $this->length = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function setWidth($value)
    {
        $this->width = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function setHeight($value)
    {
        $this->height = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param mixed $unit
     * @return $this
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUnit()
    {
        return $this->unit;
    }
}
