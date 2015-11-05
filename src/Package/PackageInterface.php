<?php

namespace EsteIt\ShippingCalculator\Package;

use EsteIt\ShippingCalculator\Address\AddressInterface;

/**
 * Interface PackageInterface
 */
interface PackageInterface
{
    /**
     * @return string
     */
    public function getWeight();

    /**
     * @return \DateTime
     */
    public function getCalculationDate();

    /**
     * @return AddressInterface
     */
    public function getSenderAddress();

    /**
     * @return AddressInterface
     */
    public function getRecipientAddress();
}
