<?php

namespace EsteIt\ShippingCalculator\Model;

use EsteIt\ShippingCalculator\Calculator\AbstractCalculator;
use EsteIt\ShippingCalculator\Exception\BasicExceptionInterface;

/**
 * Class CalculationResultInterface
 */
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
     * @param string|int|float $totalCost
     * @return $this
     */
    public function setTotalCost($totalCost);

    /**
     * @return string|int|float
     */
    public function getTotalCost();

    /**
     * @param AbstractCalculator $calculator
     * @return $this
     */
    public function setCalculator(AbstractCalculator $calculator);

    /**
     * @return AbstractCalculator
     */
    public function getCalculator();

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
