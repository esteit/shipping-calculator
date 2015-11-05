<?php

namespace EsteIt\PackageDeliveryCalculator\DeliveryMethod\Asendia;

/**
 * Class RecipientCountry
 */
class RecipientCountry
{
    /**
     * @var string|int
     */
    protected $priceGroup;

    /**
     * @var string|float|int
     */
    protected $weightLimit;

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
    public function getPriceGroup()
    {
        return $this->priceGroup;
    }

    /**
     * @param string|int $priceGroup
     * @return $this
     */
    public function setPriceGroup($priceGroup)
    {
        $this->priceGroup = $priceGroup;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getWeightLimit()
    {
        return $this->weightLimit;
    }

    /**
     * @param string|float|int $weightLimit
     * @return $this
     */
    public function setWeightLimit($weightLimit)
    {
        $this->weightLimit = $weightLimit;

        return $this;
    }
}
