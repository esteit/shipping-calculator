<?php

namespace Rage\PackageDeliveryCalculator;

use Rage\PackageDeliveryCalculator\DeliveryMethod\DeliveryMethodInterface;
use Rage\PackageDeliveryCalculator\Package\PackageInterface;

/**
 * Class CalculationResult
 */
class CalculationResult
{
    /**
     * @var DeliveryMethodInterface
     */
    protected $deliveryMethod;

    /**
     * @var PackageInterface
     */
    protected $package;

    /**
     * @var string|int|float
     */
    protected $totalCost;

    /**
     * @param string|int|float $totalCost
     * @return $this
     */
    public function setTotalCost($totalCost)
    {
        $this->totalCost = $totalCost;

        return $this;
    }

    /**
     * @return string|int|float
     */
    public function getTotalCost()
    {
        return $this->totalCost;
    }

    /**
     * @param DeliveryMethodInterface $deliveryMethod
     * @return $this
     */
    public function setDeliveryMethod(DeliveryMethodInterface $deliveryMethod)
    {
        $this->deliveryMethod = $deliveryMethod;

        return $this;
    }

    /**
     * @return DeliveryMethodInterface
     */
    public function getDeliveryMethod()
    {
        return $this->deliveryMethod;
    }

    /**
     * @param PackageInterface $package
     * @return $this
     */
    public function setPackage(PackageInterface $package)
    {
        $this->package = $package;

        return $this;
    }

    /**
     * @return PackageInterface
     */
    public function getPackage()
    {
        return $this->package;
    }
}
