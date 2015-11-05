<?php

namespace EsteIt\PackageDeliveryCalculator\Address;

/**
 * Class BaseAddress
 */
class BaseAddress implements AddressInterface
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
