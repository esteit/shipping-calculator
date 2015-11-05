<?php

namespace Rage\PackageDeliveryCalculator\Event;

use Rage\PackageDeliveryCalculator\DeliveryMethod\DeliveryMethodInterface;
use Rage\PackageDeliveryCalculator\Package\PackageInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class BeforeCalculateEvent
 */
class BeforeCalculateEvent extends Event
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
     * BeforeCalculateEvent constructor.
     *
     * @param DeliveryMethodInterface $deliveryMethod
     * @param PackageInterface        $package
     */
    public function __construct(DeliveryMethodInterface $deliveryMethod, PackageInterface $package)
    {
        $this->deliveryMethod = $deliveryMethod;
        $this->package = $package;
    }

    /**
     * @return DeliveryMethodInterface
     */
    public function getDeliveryMethod()
    {
        return $this->deliveryMethod;
    }

    /**
     * @return PackageInterface
     */
    public function getPackage()
    {
        return $this->package;
    }
}
