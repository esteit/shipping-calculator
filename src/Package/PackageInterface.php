<?php

namespace EsteIt\PackageDeliveryCalculator\Package;

use EsteIt\PackageDeliveryCalculator\Address\AddressInterface;

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
