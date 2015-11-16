<?php

namespace EsteIt\ShippingCalculator\Model;

class Address implements AddressInterface
{
    protected $countryCode;

    /**
     * @param string $countryCode
     * @return string
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }
}
