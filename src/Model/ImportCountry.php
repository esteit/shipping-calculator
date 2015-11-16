<?php

namespace EsteIt\ShippingCalculator\Model;

/**
 * Class ImportCountry
 */
class ImportCountry
{
    /**
     * @var string|int
     */
    protected $zone;

    /**
     * @var string|float|int
     */
    protected $maximumWeight;

    /**
     * @var string
     */
    protected $code;

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string|int $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string
     */
    public function getZone()
    {
        return $this->zone;
    }

    /**
     * @param string|int $zone
     * @return $this
     */
    public function setZone($zone)
    {
        $this->zone = $zone;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMaximumWeight()
    {
        return $this->maximumWeight;
    }

    /**
     * @param string|float|int $maximumWeight
     * @return $this
     */
    public function setMaximumWeight($maximumWeight)
    {
        $this->maximumWeight = $maximumWeight;

        return $this;
    }
}
