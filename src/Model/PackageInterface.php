<?php

namespace EsteIt\ShippingCalculator\Model;

/**
 * Interface PackageInterface
 */
interface PackageInterface
{
    /**
     * @return WeightInterface
     */
    public function getWeight();

    /**
     * @return AddressInterface
     */
    public function getSenderAddress();

    /**
     * @return AddressInterface
     */
    public function getRecipientAddress();

    /**
     * @return DimensionsInterface
     */
    public function getDimensions();
}
