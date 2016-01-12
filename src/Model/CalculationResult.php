<?php

namespace EsteIt\ShippingCalculator\Model;

use EsteIt\ShippingCalculator\Exception\BasicExceptionInterface;

class CalculationResult implements CalculationResultInterface
{
    /**
     * @var PackageInterface
     */
    protected $package;

    /**
     * @var string|int|float
     */
    protected $totalCost;

    /**
     * @var BasicExceptionInterface
     */
    protected $error;

    /**
     * @var string
     */
    protected $currency;

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
     * @param string $currency
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
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

    /**
     * @return BasicExceptionInterface|\Exception
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param BasicExceptionInterface $error
     * @return BasicExceptionInterface
     */
    public function setError(BasicExceptionInterface $error)
    {
        $this->error = $error;

        return $error;
    }
}
