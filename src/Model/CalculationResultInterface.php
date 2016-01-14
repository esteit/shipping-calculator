<?php

namespace EsteIt\ShippingCalculator\Model;

use EsteIt\ShippingCalculator\Exception\BasicExceptionInterface;

interface CalculationResultInterface
{
    /**
     * @param string $currency
     * @return $this
     */
    public function setCurrency($currency);

    /**
     * @return string
     */
    public function getCurrency();

    /**
     * @param string|int|float $cost
     * @return $this
     */
    public function setShippingCost($cost);

    /**
     * @return string|int|float
     */
    public function getShippingCost();

    /**
     * @param PackageInterface $package
     * @return $this
     */
    public function setPackage(PackageInterface $package);

    /**
     * @return PackageInterface
     */
    public function getPackage();

    /**
     * @return BasicExceptionInterface|\Exception
     */
    public function getError();

    /**
     * @param BasicExceptionInterface $error
     * @return BasicExceptionInterface
     */
    public function setError(BasicExceptionInterface $error);
}
